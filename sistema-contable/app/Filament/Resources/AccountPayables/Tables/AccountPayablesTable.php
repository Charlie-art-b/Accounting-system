<?php

namespace App\Filament\Resources\AccountPayables\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AccountPayablesTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                SelectFilter::make('supplier_id')
                    ->label('Proveedor')
                    ->relationship('supplier', 'nombre_razon_social')
                    ->searchable(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pending',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                        'voided' => 'Voided',
                    ]),
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'invoice' => 'Invoice',
                        'receipt' => 'Receipt',
                        'debit_note' => 'Debit note',
                        'other' => 'Other',
                    ]),
                SelectFilter::make('payment_terms')
                    ->label('Términos de Pago')
                    ->options([
                        'cash' => 'Cash',
                        'credit' => 'Credit',
                    ]),
                Filter::make('issue_date')
                
                    ->label('Fecha de Emisión')
                    ->form([
                        DatePicker::make('issue_from')->label('Desde'),
                        DatePicker::make('issue_until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['issue_from'] ?? null,
                                fn (Builder $query, $date) => $query->whereDate('issue_date', '>=', $date)
                            )
                            ->when(
                                $data['issue_until'] ?? null,
                                fn (Builder $query, $date) => $query->whereDate('issue_date', '<=', $date)
                            );
                    }),
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
                Filter::make('payment_date')
                    ->label('Fecha de Pago')
                    ->form([
                        DatePicker::make('payment_from')->label('Desde'),
                        DatePicker::make('payment_until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['payment_from'] ?? null,
                                fn (Builder $query, $date) => $query->whereDate('payment_date', '>=', $date)
                            )
                            ->when(
                                $data['payment_until'] ?? null,
                                fn (Builder $query, $date) => $query->whereDate('payment_date', '<=', $date)
                            );
                    }),
                Filter::make('amounts')
                    ->label('Montos')
                    ->form([
                        TextInput::make('total_min')
                            ->label('Total min')
                            ->numeric(),
                        TextInput::make('total_max')
                            ->label('Total max')
                            ->numeric(),
                        TextInput::make('paid_min')
                            ->label('Pagado min')
                            ->numeric(),
                        TextInput::make('paid_max')
                            ->label('Pagado max')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['total_min'] ?? null,
                                fn (Builder $query, $amount) => $query->where('total_amount', '>=', $amount)
                            )
                            ->when(
                                $data['total_max'] ?? null,
                                fn (Builder $query, $amount) => $query->where('total_amount', '<=', $amount)
                            )
                            ->when(
                                $data['paid_min'] ?? null,
                                fn (Builder $query, $amount) => $query->where('paid_amount', '>=', $amount)
                            )
                            ->when(
                                $data['paid_max'] ?? null,
                                fn (Builder $query, $amount) => $query->where('paid_amount', '<=', $amount)
                            );
                    }),
                Filter::make('pending_amount')
                    ->label('Saldo pendiente')
                    ->form([
                        TextInput::make('pending_min')
                            ->label('Saldo min')
                            ->numeric(),
                        TextInput::make('pending_max')
                            ->label('Saldo max')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['pending_min'] ?? null,
                                fn (Builder $query, $amount) => $query->whereRaw('(total_amount - paid_amount) >= ?', [$amount])
                            )
                            ->when(
                                $data['pending_max'] ?? null,
                                fn (Builder $query, $amount) => $query->whereRaw('(total_amount - paid_amount) <= ?', [$amount])
                            );
                    }),
                Filter::make('outstanding')
                    ->label('Con saldo pendiente')
                    ->query(fn (Builder $query): Builder => $query->whereColumn('paid_amount', '<', 'total_amount')),
                Filter::make('partial_only')
                    ->label('Pagos parciales')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'partial')),
                Filter::make('with_payments')
                    ->label('Con pagos')
                    ->query(fn (Builder $query): Builder => $query->where('paid_amount', '>', 0)),
                Filter::make('without_payments')
                    ->label('Sin pagos')
                    ->query(fn (Builder $query): Builder => $query->where('paid_amount', '=', 0)),
                Filter::make('due_soon')
                    ->label('Por vencer')
                    ->form([
                        TextInput::make('days')
                            ->label('Días')
                            ->numeric()
                            ->default(7),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $days = (int) ($data['days'] ?? 7);
                        $days = $days > 0 ? $days : 7;

                        return $query
                            ->whereDate('due_date', '>=', now()->toDateString())
                            ->whereDate('due_date', '<=', now()->addDays($days)->toDateString());
                    }),
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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
