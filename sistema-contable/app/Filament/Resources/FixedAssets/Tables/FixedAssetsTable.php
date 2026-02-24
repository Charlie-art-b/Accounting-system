<?php

namespace App\Filament\Resources\FixedAssets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FixedAssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('asset_name')
                    ->label('Nombre del Activo')
                    ->searchable(),
                TextColumn::make('acquisition_value')
                    ->label('Valor de Adquisición')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('acquisition_date')
                    ->label('Fecha de Adquisición')
                    ->date()
                    ->sortable(),
                TextColumn::make('useful_life_years')
                    ->label('Vida Útil (años)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('residual_value')
                    ->label('Valor Residual')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('accumulated_depreciation')
                    ->label('Depreciación Acumulada')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('net_value')
                    ->label('Valor Neto')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                TextColumn::make('disposal_date')
                    ->label('Fecha de Baja')
                    ->date()
                    ->sortable(),
                TextColumn::make('disposal_reason')
                    ->label('Motivo de Baja')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Creado en')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado en')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activo',
                        'disposed' => 'Dado de Baja',
                        'under_maintenance' => 'En Mantenimiento',
                    ]),
                
                Filter::make('acquisition_date')
                    ->label('Fecha de Adquisición')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('Desde'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('acquisition_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('acquisition_date', '<=', $date),
                            );
                    }),
                
                Filter::make('useful_life_years')
                    ->label('Vida Útil (años)')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('min')
                            ->label('Mínimo')
                            ->numeric(),
                        \Filament\Forms\Components\TextInput::make('max')
                            ->label('Máximo')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min'],
                                fn (Builder $query, $value): Builder => $query->where('useful_life_years', '>=', $value),
                            )
                            ->when(
                                $data['max'],
                                fn (Builder $query, $value): Builder => $query->where('useful_life_years', '<=', $value),
                            );
                    }),
                
                Filter::make('acquisition_value')
                    ->label('Valor de Adquisición')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('min')
                            ->label('Mínimo')
                            ->numeric(),
                        \Filament\Forms\Components\TextInput::make('max')
                            ->label('Máximo')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min'],
                                fn (Builder $query, $value): Builder => $query->where('acquisition_value', '>=', $value),
                            )
                            ->when(
                                $data['max'],
                                fn (Builder $query, $value): Builder => $query->where('acquisition_value', '<=', $value),
                            );
                    }),
                
                SelectFilter::make('has_disposal')
                    ->label('Estado de Baja')
                    ->options([
                        'yes' => 'Dados de Baja',
                        'no' => 'No Dados de Baja',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] === 'yes') {
                            return $query->whereNotNull('disposal_date');
                        } elseif ($data['value'] === 'no') {
                            return $query->whereNull('disposal_date');
                        }
                        return $query;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Eliminar activos fijos')
                        ->modalDescription('Solo se pueden eliminar activos activos sin depreciación registrada. Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar')
                        ->successNotificationTitle('Activos fijos eliminados')
                        ->before(function ($action, $records) {
                            $hasBlocked = false;

                            foreach ($records as $record) {
                                $hasDepreciation = (float) $record->accumulated_depreciation > 0;
                                $isDisposed = $record->status === 'disposed' || $record->disposal_date || $record->disposal_reason;

                                if ($isDisposed || $hasDepreciation) {
                                    $hasBlocked = true;
                                    break;
                                }
                            }

                            if ($hasBlocked) {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title('No se pueden eliminar activos fijos')
                                    ->body('Solo se pueden eliminar activos activos sin depreciación registrada.')
                                    ->persistent()
                                    ->send();

                                $action->halt();
                            }
                        }),
                ]),
            ]);
    }
}
