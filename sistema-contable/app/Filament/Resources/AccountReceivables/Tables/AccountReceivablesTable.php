<?php

namespace App\Filament\Resources\AccountReceivables\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use App\Models\AccountReceivable;


class AccountReceivablesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable(),
                TextColumn::make('invoice_number')
                    ->label('Número de factura')
                    ->searchable(),
                TextColumn::make('issue_date')
                    ->label('Fecha de emisión')
                    ->date()
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Fecha de vencimiento')
                    ->date()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable(),
                TextColumn::make('total_amount')
                    ->label('Monto total')
                    ->money('CRC')
                    //->numeric()
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Monto pagado')
                    ->money('CRC')
                    //->numeric()
                    ->sortable(),
                TextColumn::make('pending_amount')
                    ->label('Monto pendiente')
                    //->numeric()
                    ->money('CRC')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->state(fn (AccountReceivable $record) => $record->status)
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pendiente',
                        'partial' => 'Parcial',
                        'paid' => 'Pagado',
                        default => $state,
                    })
                    ->colors([
                        'danger' => 'pending',
                        'warning' => 'partial',
                        'success' => 'paid',
                    ])
                    ->badge(),
                TextColumn::make('created_at')
                     ->label('Creado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'partial' => 'Parcial',
                        'paid'    => 'Pagado',
                    ]),

                SelectFilter::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('dates')
                    ->label('Fechas')
                    ->form([
                       
                        DatePicker::make('issue_from')->label('Desde (emisión)'),
                        DatePicker::make('issue_until')->label('Hasta (emisión)'),

                        DatePicker::make('due_from')->label('Desde (vencimiento)'),
                        DatePicker::make('due_until')->label('Hasta (vencimiento)'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['issue_from'] ?? null, fn (Builder $q, $d) => $q->whereDate('issue_date', '>=', $d))
                            ->when($data['issue_until'] ?? null, fn (Builder $q, $d) => $q->whereDate('issue_date', '<=', $d))
                            ->when($data['due_from'] ?? null, fn (Builder $q, $d) => $q->whereDate('due_date', '>=', $d))
                            ->when($data['due_until'] ?? null, fn (Builder $q, $d) => $q->whereDate('due_date', '<=', $d));
                    }),


                Filter::make('pending')
                    ->label('Pendientes')
                    ->query(fn (Builder $query) =>
                    $query->where('status', 'pending')
                    ),

                Filter::make('partial')
                    ->label('Parciales')
                    ->query(fn (Builder $query) =>
                     $query->where('status', 'partial')
                     ),

                Filter::make('paid')
                    ->label('Pagados')
                    ->query(fn (Builder $query) =>
                    $query->where('status', 'paid')
                    ),
                
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records, DeleteBulkAction $action) {

                            $blocked = $records->filter(
                                fn ($r) => in_array($r->status, ['pending', 'partial'], true)
                            );

                            if ($blocked->isNotEmpty()) {
                                Notification::make()
                                    ->title('NO SE PUEDE ELIMINAR')
                                    ->body('Seleccionaste cuentas en estado Pendiente o Parcial. Solo se pueden eliminar cuentas ya Pagadas.')
                                    ->danger()
                                    ->send();

                                $action->halt();
                            }
                        }),
                ]),
            ]);
    }
}
