<?php

namespace App\Filament\Resources\AccountPayables\Tables;

use App\Filament\Support\CrudImportExportActions;
use App\Models\AccountPayable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Tables\Actions\DeleteBulkAction as TableDeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Services\PaymentService;

class AccountPayablesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('supplier'))
            ->columns([
                TextColumn::make('supplier.nombre_razon_social')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('document_number')
                    ->label('N° Documento')
                    ->searchable(),
                TextColumn::make('issue_date')
                    ->label('Fecha de Emisión')
                    ->date()
                    ->sortable(),
                TextColumn::make('payment_terms')
                    ->label('Términos de Pago')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'cash' => 'Efectivo',
                        'credit' => 'Crédito',
                        default => $state,
                    })
                    ->badge(),
                TextColumn::make('payment_period')
                    ->label('Período de Pago')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Fecha de Vencimiento')
                    ->date()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'invoice' => 'Factura',
                        'receipt' => 'Recibo',
                        'debit_note' => 'Nota de débito',
                        'other' => 'Otro',
                        default => $state,
                    })
                    ->badge(),
                TextColumn::make('total_amount')
                    ->label('Monto Total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Monto Pagado')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pending_amount')
                    ->label('Saldo Pendiente')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($record) => $record->pending_amount > 0 ? 'warning' : 'success'),
                TextColumn::make('payment_date')
                    ->label('Fecha de Pago')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pendiente',
                        'partial' => 'Parcial',
                        'paid' => 'Pagado',
                        'voided' => 'Anulado',
                        default => $state,
                    })
                    ->badge()
                    ->colors([
                        'danger' => 'pending',
                        'warning' => 'partial',
                        'success' => 'paid',
                        'gray' => 'voided',
                    ]),
                TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Fecha de Actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filtros principales por categoría
                SelectFilter::make('supplier_id')
                    ->label('Proveedor')
                    ->relationship('supplier', 'nombre_razon_social')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'partial' => 'Parcial',
                        'paid' => 'Pagado',
                    ]),
                SelectFilter::make('payment_terms')
                    ->label('Términos de Pago')
                    ->options([
                        'cash' => 'Efectivo',
                        'credit' => 'Crédito',
                    ]),

                // Filtro de vencimiento (crítico para gestión de pagos)
                Filter::make('due_date')
                    ->label('Fecha de Vencimiento')
                    ->form([
                        DatePicker::make('due_from')->label('Desde'),
                        DatePicker::make('due_until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['due_from'] ?? null,
                                fn (Builder $query, $date) => $query->whereDate('due_date', '>=', $date)
                            )
                            ->when(
                                $data['due_until'] ?? null,
                                fn (Builder $query, $date) => $query->whereDate('due_date', '<=', $date)
                            );
                    }),

                // Filtros de estado de pago (críticos)
                Filter::make('outstanding')
                    ->label('Con saldo pendiente')
                    ->query(fn (Builder $query): Builder => $query->whereColumn('paid_amount', '<', 'total_amount')),

                Filter::make('overdue')
                    ->label('Vencidas')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereDate('due_date', '<', now()->toDateString())
                        ->whereNotIn('status', ['paid', 'voided'])),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record) => $record->status !== 'paid'),
                
                // Registrar pago
                Action::make('pay')
                    ->label('Registrar pago')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Registrar pago')
                    ->modalDescription('Este pago actualizará el monto pagado de la cuenta por pagar')
                    ->form(function (AccountPayable $record) {
                        $pendingAmount = $record->pending_amount ?? 0;
                        $minDate = $record->issue_date 
                            ? \Illuminate\Support\Carbon::parse($record->issue_date)->format('Y-m-d')
                            : null;

                        return [
                            TextInput::make('amount')
                                ->label('Monto a pagar')
                                ->numeric()
                                ->minValue(0.01)
                                ->maxValue($pendingAmount)
                                ->helperText("Pendiente: $" . number_format($pendingAmount, 2))
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
                    ->visible(fn (AccountPayable $record) => 
                        $record->status !== 'paid' && $record->status !== 'voided'
                    )
                    ->action(function (AccountPayable $record, array $data) {
                        // Evitar pagar si ya está pagada
                        if ($record->status === 'paid') {
                            Notification::make()
                                ->title('Esta cuenta ya está pagada')
                                ->warning()
                                ->send();
                            return;
                        }

                        try {
                            $service = new PaymentService();
                            $payable = AccountPayable::find($record->id);

                            if (! $payable) {
                                throw new \Exception('No se pudo obtener el registro.');
                            }

                            $payment = $service->createPayment($payable, (float) $data['amount'], $data['paid_at'], $data['note'] ?? null);

                            // Registrar en notas y actualizar last_action
                            $paymentLog = sprintf(
                                "%s|%.2f|%s",
                                $data['paid_at'],
                                (float) $data['amount'],
                                $data['note'] ?? 'Sin nota'
                            );

                            $payable->notes = trim(($payable->notes ?? '') . "\n" . $paymentLog);
                            $payable->save();

                            Notification::make()
                                ->title('Pago registrado exitosamente')
                                ->body('Monto: $' . number_format((float) $data['amount'], 2))
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
                    ->form(function (AccountPayable $record) {
                        $paymentsOptions = [];

                        $payable = $record;
                        $payments = $payable->payments()->where('is_reversal', false)->whereDoesntHave('reversal')->orderBy('paid_at', 'desc')->get();
                        foreach ($payments as $p) {
                            $label = sprintf('📅 %s - $%s - %s', optional($p->paid_at)->format('Y-m-d'), number_format((float)$p->amount, 2), $p->note ?? '');
                            $paymentsOptions['payment_' . $p->id] = $label;
                        }

                        // Fallback legacy notes only if there are no rows in payments table
                        if (empty($paymentsOptions) && !$payable->payments()->exists() && !empty($record->notes)) {
                            $lines = explode("\n", trim($record->notes));
                            foreach ($lines as $index => $line) {
                                if (str_contains($line, '|')) {
                                    $parts = explode('|', $line, 3);
                                    if (count($parts) === 3) {
                                        $paymentsOptions['note_' . $index] = sprintf(
                                            "📅 %s - $%s - %s",
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
                                    ->helperText('Esta cuenta no tiene pagos que se puedan deshacer'),
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
                    ->visible(function (AccountPayable $record) {
                        // Visible sólo si hay pagos elegibles en la tabla payments o notas legacy
                        $hasEligiblePayments = $record->payments()->where('is_reversal', false)->whereDoesntHave('reversal')->exists();
                        if ($hasEligiblePayments) return true;

                        // Si hay cualquier pago histórico en la tabla (incluso reversos), ocultar la acción
                        $hasAnyPayments = $record->payments()->exists();
                        if ($hasAnyPayments) return false;

                        if (empty($record->notes)) return false;
                        $lines = explode("\n", trim($record->notes));
                        foreach ($lines as $line) {
                            if (str_contains($line, '|')) {
                                return true; // Hay al menos un pago legacy
                            }
                        }
                        return false;
                    })
                    ->action(function (AccountPayable $record, array $data) {
                        if (!isset($data['payment_index'])) {
                            Notification::make()
                                ->title('Debe seleccionar un pago')
                                ->warning()
                                ->send();
                            return;
                        }
                        try {
                            $service = new PaymentService();
                            $sel = $data['payment_index'];

                            if (str_starts_with($sel, 'payment_')) {
                                $id = (int) str_replace('payment_', '', $sel);
                                $payment = \App\Models\Payment::find($id);
                                if (! $payment) {
                                    throw new \Exception('Pago no encontrado.');
                                }

                                $service->reversePayment($payment, auth()->id() ?? null);

                                $record->notes = trim(($record->notes ?? '') . "\nReverso de pago #{$payment->id}");
                                $record->save();

                                Notification::make()->title('Pago deshecho exitosamente')->success()->send();
                                return;
                            }

                            if (str_starts_with($sel, 'note_')) {
                                DB::transaction(function () use ($record, $sel) {
                                    $rec = AccountPayable::where('id', $record->id)->lockForUpdate()->first();

                                    if (!$rec) {
                                        throw new \Exception('No se pudo obtener el registro.');
                                    }

                                    $lines = explode("\n", trim($rec->notes));
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

                                    if ($paymentAmount > $rec->paid_amount) {
                                        throw new \Exception('El monto a deshacer excede el monto pagado actual.');
                                    }

                                    $rec->paid_amount = (float) $rec->paid_amount - $paymentAmount;

                                    unset($lines[$paymentIndex]);
                                    $rec->notes = implode("\n", array_values($lines));

                                    if (empty(trim($rec->notes))) {
                                        $rec->payment_date = null;
                                    }

                                    $rec->save();
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
            ])
            ->toolbarActions([
                ...CrudImportExportActions::make(
                    modelClass: AccountPayable::class,
                    title: 'Cuentas por Pagar',
                    filePrefix: 'cuentas-por-pagar',
                    fields: [
                        'supplier_id',
                        'document_number',
                        'issue_date',
                        'payment_terms',
                        'payment_period',
                        'due_date',
                        'type',
                        'total_amount',
                        'paid_amount',
                        'payment_date',
                        'status',
                        'notes',
                    ],
                    uniqueBy: ['document_number', 'supplier_id'],
                    defaults: [
                        'paid_amount' => 0,
                        'status' => 'pending',
                    ],
                    enumMaps: [
                        'payment_terms' => [
                            'cash' => 'cash',
                            'efectivo' => 'cash',
                            'credit' => 'credit',
                            'credito' => 'credit',
                            'crédito' => 'credit',
                        ],
                        'type' => [
                            'invoice' => 'invoice',
                            'factura' => 'invoice',
                            'receipt' => 'receipt',
                            'recibo' => 'receipt',
                            'debit_note' => 'debit_note',
                            'nota_debito' => 'debit_note',
                            'nota de debito' => 'debit_note',
                            'other' => 'other',
                            'otro' => 'other',
                        ],
                        'status' => [
                            'pending' => 'pending',
                            'pendiente' => 'pending',
                            'partial' => 'partial',
                            'parcial' => 'partial',
                            'paid' => 'paid',
                            'pagado' => 'paid',
                            'voided' => 'voided',
                            'anulado' => 'voided',
                        ],
                    ],
                    requiredFields: ['supplier_id', 'document_number', 'total_amount'],
                    fieldLabels: [
                        'supplier.nombre_razon_social' => 'Proveedor',
                        'document_number' => 'N° Documento',
                        'issue_date' => 'Fecha de Emisión',
                        'payment_terms' => 'Términos de Pago',
                        'payment_period' => 'Período de Pago',
                        'due_date' => 'Fecha de Vencimiento',
                        'type' => 'Tipo',
                        'total_amount' => 'Monto Total',
                        'paid_amount' => 'Monto Pagado',
                        'payment_date' => 'Fecha de Pago',
                        'status' => 'Estado',
                        'notes' => 'Notas',
                    ],
                    exportFields: [
                        'supplier.nombre_razon_social',
                        'document_number',
                        'issue_date',
                        'payment_terms',
                        'payment_period',
                        'due_date',
                        'type',
                        'total_amount',
                        'paid_amount',
                        'payment_date',
                        'status',
                        'notes',
                    ],
                ),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records, DeleteBulkAction $action) {
                            $blockedCount = 0;
                            $blockedReasons = [];

                            foreach ($records as $account) {
                                if (!in_array($account->status, ['voided', 'paid'])) {
                                    $blockedCount++;
                                    $statusLabel = match($account->status) {
                                        'pending' => 'Pendiente',
                                        'partial' => 'Parcial',
                                        default => $account->status,
                                    };
                                    $blockedReasons[] = "{$account->document_number} ({$account->supplier->nombre_razon_social}): Estado {$statusLabel}";
                                }
                            }

                            if ($blockedCount > 0) {
                                $reasonsList = implode("\n• ", $blockedReasons);
                                Notification::make()
                                    ->title('NO SE PUEDE ELIMINAR')
                                    ->body("No se pueden eliminar {$blockedCount} cuenta(s):\n\n• {$reasonsList}\n\nSolo se pueden eliminar cuentas en estado Pagado o Anulado.")
                                    ->danger()
                                    ->send();

                                $action->halt();
                            }
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('¡Cuenta(s) eliminada(s)!')
                                ->body('La(s) cuenta(s) por pagar han sido eliminada(s) correctamente.')
                        ),
                ]),
            ])
            ->defaultSort('due_date', 'asc')
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100]);
    }
}
