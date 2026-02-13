<?php

namespace App\Filament\Resources\CollectionManagement\Tables;

use App\Models\CollectionManagement;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
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
                        'overdue' => 'Vencido',
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
                            DB::transaction(function () use ($record, $data) {
                                $ar = $record->accountReceivable()->lockForUpdate()->first();

                                if (! $ar) {
                                    throw new \Exception('No existe la cuenta por cobrar asociada.');
                                }

                                // Validaciones de fecha de pago
                                $paidAt = \Illuminate\Support\Carbon::parse($data['paid_at']);
                                
                                if ($paidAt->gt(now())) {
                                    throw new \Exception('La fecha de pago no puede ser futura.');
                                }
                                
                                if (!empty($ar->issue_date) && $paidAt->lt(\Illuminate\Support\Carbon::parse($ar->issue_date))) {
                                    throw new \Exception('La fecha de pago no puede ser anterior a la fecha de emisión.');
                                }

                                $pending = (float) $ar->total_amount - (float) $ar->paid_amount;
                                $pay = (float) $data['amount'];

                                if ($pay <= 0) {
                                    throw new \Exception('El monto debe ser mayor que 0.');
                                }

                                if ($pay > $pending) {
                                    throw new \Exception("El pago excede el pendiente. Pendiente: ₡" . number_format($pending, 2));
                                }

                                // Validación de pagos duplicados: mismo monto, fecha y nota
                                $paymentSignature = sprintf(
                                    "%s|%.2f|%s",
                                    $data['paid_at'],
                                    $pay,
                                    $data['note'] ?? 'Sin nota'
                                );
                                
                                if (str_contains($record->notes ?? '', $paymentSignature)) {
                                    throw new \Exception('Pago duplicado: Ya existe un pago con el mismo monto, fecha y nota.');
                                }

                                // Actualizar monto pagado
                                $ar->paid_amount = (float) $ar->paid_amount + $pay;

                                // Recalcular estado de la cuenta por cobrar
                                if ($ar->paid_amount <= 0) {
                                    $ar->status = 'pending';
                                } elseif ($ar->paid_amount < $ar->total_amount) {
                                    $ar->status = 'partial';
                                } else {
                                    $ar->status = 'paid';
                                }

                                $ar->save();

                                // Actualizar la gestión con registro del pago
                                $record->last_action = 'Pago registrado: ₡' . number_format($pay, 2);
                                
                                // Registrar en notas con formato estructurado para evitar duplicados
                                $paymentLog = sprintf(
                                    "%s|%.2f|%s",
                                    $data['paid_at'],
                                    $pay,
                                    $data['note'] ?? 'Sin nota'
                                );
                                $record->notes = trim(($record->notes ?? '') . "\n" . $paymentLog);

                                // Si quedó pagada, apagamos recordatorio
                                if ($ar->status === 'paid') {
                                    $record->next_reminder_at = null;
                                }

                                $record->save();
                            });

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

                ViewAction::make(),
            ])
            ->toolbarActions([
                // No hay acciones de creación ni eliminación
            ])
            ->defaultSort('next_reminder_at', 'asc');
    }
}