<?php

namespace App\Filament\Resources\AccountPayables\Tables;

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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
                    ->badge(),
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
                        'voided' => 'Anulado',
                    ]),

                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'invoice' => 'Factura',
                        'receipt' => 'Recibo',
                        'debit_note' => 'Nota de débito',
                        'other' => 'Otro',
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
                            DB::transaction(function () use ($record, $data) {
                                $record = AccountPayable::where('id', $record->id)->lockForUpdate()->first();

                                if (!$record) {
                                    throw new \Exception('No se pudo obtener el registro.');
                                }

                                // Validaciones de fecha de pago
                                $paidAt = \Illuminate\Support\Carbon::parse($data['paid_at']);
                                
                                if ($paidAt->gt(now())) {
                                    throw new \Exception('La fecha de pago no puede ser futura.');
                                }
                                
                                if (!empty($record->issue_date) && $paidAt->lt(\Illuminate\Support\Carbon::parse($record->issue_date))) {
                                    throw new \Exception('La fecha de pago no puede ser anterior a la fecha de emisión.');
                                }

                                $pending = (float) $record->total_amount - (float) $record->paid_amount;
                                $pay = (float) $data['amount'];

                                if ($pay <= 0) {
                                    throw new \Exception('El monto debe ser mayor que 0.');
                                }

                                if ($pay > $pending) {
                                    throw new \Exception("El pago excede el pendiente. Pendiente: $" . number_format($pending, 2));
                                }

                                // Validación de pagos duplicados
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
                                $record->paid_amount = (float) $record->paid_amount + $pay;
                                
                                // Actualizar payment_date si corresponde
                                $record->payment_date = $data['paid_at'];

                                // Registrar en notas
                                $paymentLog = sprintf(
                                    "%s|%.2f|%s",
                                    $data['paid_at'],
                                    $pay,
                                    $data['note'] ?? 'Sin nota'
                                );
                                $record->notes = trim(($record->notes ?? '') . "\n" . $paymentLog);

                                // El observer del modelo se encargará de actualizar el status automáticamente
                                $record->save();
                            });

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
                        // Parsear pagos registrados del campo notes
                        $payments = [];
                        if (!empty($record->notes)) {
                            $lines = explode("\n", trim($record->notes));
                            foreach ($lines as $index => $line) {
                                if (str_contains($line, '|')) {
                                    $parts = explode('|', $line, 3);
                                    if (count($parts) === 3) {
                                        $payments[$index] = sprintf(
                                            "📅 %s - $%s - %s",
                                            $parts[0],
                                            number_format((float)$parts[1], 2),
                                            $parts[2]
                                        );
                                    }
                                }
                            }
                        }

                        if (empty($payments)) {
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
                                ->options($payments)
                                ->required()
                                ->searchable()
                                ->helperText('⚠️ Esta acción no se puede deshacer'),
                        ];
                    })
                    ->visible(function (AccountPayable $record) {
                        // Ocultar si no hay pagos registrados
                        if (empty($record->notes)) return false;
                        
                        $lines = explode("\n", trim($record->notes));
                        foreach ($lines as $line) {
                            if (str_contains($line, '|')) {
                                return true; // Hay al menos un pago
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
                            DB::transaction(function () use ($record, $data) {
                                $record = AccountPayable::where('id', $record->id)->lockForUpdate()->first();

                                if (!$record) {
                                    throw new \Exception('No se pudo obtener el registro.');
                                }

                                // Parsear las notas
                                $lines = explode("\n", trim($record->notes));
                                $paymentIndex = (int) $data['payment_index'];

                                if (!isset($lines[$paymentIndex])) {
                                    throw new \Exception('El pago seleccionado no existe.');
                                }

                                $paymentLine = $lines[$paymentIndex];
                                $parts = explode('|', $paymentLine, 3);

                                if (count($parts) !== 3) {
                                    throw new \Exception('Formato de pago inválido.');
                                }

                                $paymentAmount = (float) $parts[1];

                                // Validar que el monto a deshacer no exceda el pagado
                                if ($paymentAmount > $record->paid_amount) {
                                    throw new \Exception('El monto a deshacer excede el monto pagado actual.');
                                }

                                // Revertir el pago
                                $record->paid_amount = (float) $record->paid_amount - $paymentAmount;

                                // Remover la línea del pago de las notas
                                unset($lines[$paymentIndex]);
                                $record->notes = implode("\n", $lines);

                                // Si no quedan pagos, limpiar payment_date
                                if (empty(trim($record->notes))) {
                                    $record->payment_date = null;
                                }

                                // El observer actualizará automáticamente el status
                                $record->save();
                            });

                            Notification::make()
                                ->title('Pago deshecho exitosamente')
                                ->success()
                                ->send();
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
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('due_date', 'asc')
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100]);
    }
}
