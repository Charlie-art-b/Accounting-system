<?php

namespace App\Filament\Resources\CollectionManagement\Tables;

use App\Filament\Support\CrudImportExportActions;
use App\Models\CollectionManagement;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use App\Services\PaymentService;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class CollectionManagementTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // FACTURA 
                TextColumn::make('accountReceivable.invoice_number')
                    ->label('Factura')
                    ->searchable()
                    ->sortable(),

                // CLIENTE
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                // VENCIMIENTO
                TextColumn::make('accountReceivable.due_date')
                    ->label('Vencimiento')
                    ->date()
                    ->sortable(),

                // DÍAS DE RETRASO (derivado)
                TextColumn::make('days_late')
                    ->label('Días atraso')
                    ->alignCenter()
                    ->state(fn (CollectionManagement $record) => $record->days_late)
                    ->sortable(),

                // MONTO PENDIENTE (derivado)
                TextColumn::make('pending_amount')
                    ->label('Monto pendiente')
                    ->money('CRC')
                    ->state(fn (CollectionManagement $record) => $record->pending_amount)
                    ->sortable(),

                // ESTADO (derivado)
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->state(fn (CollectionManagement $record) => $record->status)
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'overdue' => 'Plazo Vencido',
                        'due_soon' => 'Próximo a vencer',
                        default => 'Pendiente',
                    })
                    ->colors([
                        'danger' => 'overdue',
                        'warning' => 'due_soon',
                        'success' => 'pending',
                    ]),

                /*// PRÓXIMO RECORDATORIO
                TextColumn::make('next_reminder_at')
                    ->label('Próximo recordatorio')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),

                // INTENTOS
                TextColumn::make('reminder_attempts')
                    ->label('Intentos')
                    ->numeric()
                    ->sortable(),
                */
                    
                TextColumn::make('last_action')
                    ->label('Última acción')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('due_date_range')
                ->label('Vencimiento')
                ->form([
                    DatePicker::make('from')->label('Desde (vencimiento)'),
                    DatePicker::make('until')->label('Hasta (vencimiento)'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query->whereHas('accountReceivable', function (Builder $q) use ($data) {
                        return $q
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('due_date', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('due_date', '<=', $date));
                    });
                }),
            ])
            ->recordActions([
                // Registrar pago (actualiza accounts_receivable.paid_amount SOLO desde cobros)
                Action::make('pay')
                    ->label('Registrar pago')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Registrar pago')
                    ->modalDescription('Este pago actualizará el monto pagado de la cuenta por cobrar')
                    ->form(function (CollectionManagement $record) {
                        $pendingAmount = $record->pending_amount ?? 0;
                        $minDate = $record->accountReceivable?->issue_date 
                            ? \Illuminate\Support\Carbon::parse($record->accountReceivable->issue_date)->format('Y-m-d')
                            : null;

                        return [
                            TextInput::make('amount')
                                ->label('Monto a pagar')
                                ->numeric()
                                ->minValue(0.01)
                                ->maxValue($pendingAmount)
                                ->helperText("Pendiente: ₡" . number_format($pendingAmount, 2))
                                ->required(),

                            DatePicker::make('paid_at')
                                ->label('Fecha de pago')
                                ->default(now())
                                ->maxDate(now())
                                ->minDate($minDate)
                                ->helperText($minDate ? "No puede ser anterior a la emisión ({$minDate})" : 'No puede ser fecha futura')
                                ->required(),

                            Textarea::make('note')
                                ->label('Nota (opcional)')
                                ->rows(3)
                                ->helperText('Descripción del pago o método utilizado'),
                        ];
                    })
                    ->hidden(fn (CollectionManagement $record) => !$record->accountReceivable)
                    ->action(function (CollectionManagement $record, array $data) {
                        // Evitar pagar si ya está pagada
                        if ($record->accountReceivable?->status === 'paid') {
                            Notification::make()
                                ->title('Esta cuenta ya está pagada')
                                ->warning()
                                ->send();
                            return;
                        }

                        try {
                            $service = new PaymentService();
                            $ar = $record->accountReceivable;

                            if (! $ar) {
                                throw new \Exception('No existe la cuenta por cobrar asociada.');
                            }

                            $payment = $service->createPayment($ar, (float) $data['amount'], $data['paid_at'], $data['note'] ?? null);

                            // Registrar la gestión con registro del pago (nota y última acción)
                            $paymentLog = sprintf(
                                "%s|%.2f|%s",
                                $data['paid_at'],
                                (float) $data['amount'],
                                $data['note'] ?? 'Sin nota'
                            );

                            $record->last_action = 'Pago registrado: ₡' . number_format((float) $data['amount'], 2);
                            $record->notes = trim(($record->notes ?? '') . "\n" . $paymentLog);

                            if ($ar->status === 'paid') {
                                $record->next_reminder_at = null;
                            }

                            $record->save();

                            Notification::make()
                                ->title('Pago registrado exitosamente')
                                ->body('Monto: ₡' . number_format((float) $data['amount'], 2))
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error al registrar el pago')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // Deshacer pago
                Action::make('undo_payment')
                    ->label('Deshacer pago')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Deshacer pago registrado')
                    ->modalDescription('Esta acción revertirá el pago seleccionado y actualizará el estado de la cuenta')
                    ->form(function (CollectionManagement $record) {
                        $paymentsOptions = [];

                        $ar = $record->accountReceivable;
                        if ($ar) {
                            $payments = $ar->payments()->where('is_reversal', false)->whereDoesntHave('reversal')->orderBy('paid_at', 'desc')->get();
                            foreach ($payments as $p) {
                                $label = sprintf('📅 %s - ₡%s - %s', optional($p->paid_at)->format('Y-m-d'), number_format((float)$p->amount, 2), $p->note ?? '');
                                $paymentsOptions['payment_' . $p->id] = $label;
                            }
                        }

                        // Fallback a notas legacy si no hay pagos en la tabla
                        if (empty($paymentsOptions) && !empty($record->notes)) {
                            $lines = explode("\n", trim($record->notes));
                            foreach ($lines as $index => $line) {
                                if (str_contains($line, '|')) {
                                    $parts = explode('|', $line, 3);
                                    if (count($parts) === 3) {
                                        $paymentsOptions['note_' . $index] = sprintf(
                                            "📅 %s - ₡%s - %s",
                                            $parts[0],
                                            number_format((float)$parts[1], 2),
                                            $parts[2]
                                        );
                                    }
                                }
                            }
                        }

                        if (empty($paymentsOptions)) {
                            return [
                                Select::make('payment_index')
                                    ->label('No hay pagos registrados')
                                    ->disabled()
                                    ->options([])
                                    ->helperText('Este cobro no tiene pagos que se puedan deshacer'),
                            ];
                        }

                        return [
                            Select::make('payment_index')
                                ->label('Seleccionar pago a deshacer')
                                ->options($paymentsOptions)
                                ->required()
                                ->searchable()
                                ->helperText('⚠️ Esta acción no se puede deshacer'),
                        ];
                    })
                    ->hidden(function (CollectionManagement $record) {
                        // Ocultar si no hay pagos elegibles en la tabla payments y tampoco notas legacy
                        // Si la tabla payments tiene cualquier fila (incluso reversos), no mostrar fallback a notas.
                        $ar = $record->accountReceivable;
                        $hasEligiblePayments = $ar && $ar->payments()->where('is_reversal', false)->whereDoesntHave('reversal')->exists();
                        if ($hasEligiblePayments) return false;

                        // Si hay cualquier pago histórico en la tabla (incluso reversos), ocultar la acción
                        $hasAnyPayments = $ar && $ar->payments()->exists();
                        if ($hasAnyPayments) return true;

                        if (empty($record->notes)) return true;
                        $lines = explode("\n", trim($record->notes));
                        foreach ($lines as $line) {
                            if (str_contains($line, '|')) {
                                return false; // Hay al menos un pago legacy
                            }
                        }
                        return true;
                    })
                    ->action(function (CollectionManagement $record, array $data) {
                        if (!isset($data['payment_index'])) {
                            Notification::make()
                                ->title('Debe seleccionar un pago')
                                ->warning()
                                ->send();
                            return;
                        }
                        try {
                            $service = new \App\Services\PaymentService();

                            // Detect whether selection is a payment id (payment_{id}) or legacy note (note_{index})
                            $sel = $data['payment_index'];

                            if (str_starts_with($sel, 'payment_')) {
                                $id = (int) str_replace('payment_', '', $sel);
                                $payment = \App\Models\Payment::find($id);

                                if (! $payment) {
                                    throw new \Exception('Pago no encontrado.');
                                }

                                $service->reversePayment($payment, auth()->id() ?? null);

                                $record->last_action = sprintf('Pago deshecho: ₡%s (id: %s)', number_format((float)$payment->amount, 2), $payment->id);
                                $record->save();

                                Notification::make()->title('Pago deshecho exitosamente')->success()->send();
                                return;
                            }

                            // Fallback legacy notes logic (note_{index})
                            if (str_starts_with($sel, 'note_')) {
                                DB::transaction(function () use ($record, $sel) {
                                    $ar = $record->accountReceivable()->lockForUpdate()->first();

                                    if (!$ar) {
                                        throw new \Exception('No existe la cuenta por cobrar asociada.');
                                    }

                                    $lines = explode("\n", trim($record->notes));
                                    $paymentIndex = (int) str_replace('note_', '', $sel);

                                    if (!isset($lines[$paymentIndex])) {
                                        throw new \Exception('El pago seleccionado no existe.');
                                    }

                                    $paymentLine = $lines[$paymentIndex];
                                    $parts = explode('|', $paymentLine, 3);

                                    if (count($parts) !== 3) {
                                        throw new \Exception('Formato de pago inválido.');
                                    }

                                    $paymentAmount = (float) $parts[1];

                                    if ($ar->paid_amount < $paymentAmount) {
                                        throw new \Exception('No se puede deshacer: el monto pagado actual es menor al pago registrado.');
                                    }

                                    $ar->paid_amount = (float) $ar->paid_amount - $paymentAmount;

                                    if ($ar->paid_amount <= 0) {
                                        $ar->status = 'pending';
                                    } elseif ($ar->paid_amount < $ar->total_amount) {
                                        $ar->status = 'partial';
                                    } else {
                                        $ar->status = 'paid';
                                    }

                                    $ar->save();

                                    unset($lines[$paymentIndex]);
                                    $record->notes = implode("\n", array_values($lines));

                                    $record->last_action = sprintf('Pago deshecho: ₡%s', number_format($paymentAmount, 2));
                                    $record->save();
                                });

                                Notification::make()->title('Pago deshecho exitosamente')->success()->send();
                                return;
                            }

                            throw new \Exception('Selección de pago inválida.');
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error al deshacer el pago')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                ViewAction::make(),
            ])
            ->toolbarActions([
                ...CrudImportExportActions::make(
                    modelClass: CollectionManagement::class,
                    title: 'Gestion de Cobro',
                    filePrefix: 'gestion-cobro',
                    fields: [
                        'account_receivable_id',
                        'customer_id',
                        'next_reminder_at',
                        'reminder_attempts',
                        'last_action',
                        'notes',
                    ],
                    uniqueBy: ['account_receivable_id'],
                    defaults: ['reminder_attempts' => 0],
                    fieldLabels: [
                        'accountReceivable.invoice_number' => 'Factura',
                        'customer.name' => 'Cliente',
                        'next_reminder_at' => 'Próximo Recordatorio',
                        'reminder_attempts' => 'Intentos de Recordatorio',
                        'last_action' => 'Última Acción',
                        'notes' => 'Notas',
                    ],
                    exportFields: [
                        'accountReceivable.invoice_number',
                        'customer.name',
                        'next_reminder_at',
                        'reminder_attempts',
                        'last_action',
                        'notes',
                    ],
                ),
            ])
            ->defaultSort('next_reminder_at', 'asc');
    }
}
