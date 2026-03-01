<?php

namespace App\Filament\Resources\FixedAssets\Tables;

use App\Filament\Support\CrudImportExportActions;
use App\Models\FixedAsset;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class FixedAssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('asset_name')
                    ->label('Activo')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'active' => 'Activo',
                        'disposed' => 'Dado de baja',
                        'under_maintenance' => 'En mantenimiento',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success',
                        'disposed' => 'danger',
                        'under_maintenance' => 'warning',
                    }),

                TextColumn::make('acquisition_date')
                    ->label('Adquisicion')
                    ->date()
                    ->sortable(),

                TextColumn::make('acquisition_value')
                    ->label('Valor adquisicion')
                    ->numeric()
                    ->money('CRC')
                    ->sortable(),

                TextColumn::make('net_value')
                    ->label('Valor neto')
                    ->numeric()
                    ->money('CRC')
                    ->sortable(),

                TextColumn::make('accumulated_depreciation')
                    ->label('Depreciacion')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('useful_life_years')
                    ->label('Vida util')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('disposal_date')
                    ->label('Fecha baja')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('disposal_reason')
                    ->label('Motivo baja')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activo',
                        'disposed' => 'Dado de baja',
                        'under_maintenance' => 'En mantenimiento',
                    ]),

                Filter::make('acquisition_date')
                    ->label('Fecha de adquisicion')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Desde'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date) => $query->whereDate('acquisition_date', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, $date) => $query->whereDate('acquisition_date', '<=', $date));
                    }),

                Filter::make('useful_life_years')
                    ->label('Vida util')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('min')->label('Minimo')->numeric(),
                        \Filament\Forms\Components\TextInput::make('max')->label('Maximo')->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['min'] ?? null, fn (Builder $query, $value) => $query->where('useful_life_years', '>=', $value))
                            ->when($data['max'] ?? null, fn (Builder $query, $value) => $query->where('useful_life_years', '<=', $value));
                    }),

                Filter::make('acquisition_value')
                    ->label('Valor adquisicion')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('min')->label('Minimo')->numeric(),
                        \Filament\Forms\Components\TextInput::make('max')->label('Maximo')->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['min'] ?? null, fn (Builder $query, $value) => $query->where('acquisition_value', '>=', $value))
                            ->when($data['max'] ?? null, fn (Builder $query, $value) => $query->where('acquisition_value', '<=', $value));
                    }),
            ])
            ->recordActions([
                ViewAction::make()->visible(fn () => Auth::user()?->can('fixed_assets.view') ?? false),
                EditAction::make()->visible(fn () => Auth::user()?->can('fixed_assets.update') ?? false),
                DeleteAction::make()
                    ->visible(fn () => Auth::user()?->can('fixed_assets.delete') ?? false)
                    ->before(function (FixedAsset $record, DeleteAction $action) {
                        $hasDepreciation = (float) $record->accumulated_depreciation > 0;
                        $isDisposed = $record->status === 'disposed' || $record->disposal_date || $record->disposal_reason;

                        if ($isDisposed || $hasDepreciation) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('No se puede eliminar')
                                ->body('Solo se pueden eliminar activos activos sin depreciacion registrada.')
                                ->persistent()
                                ->send();

                            $action->halt();
                        }
                    }),
            ])
            ->toolbarActions([
                ...CrudImportExportActions::make(
                    modelClass: FixedAsset::class,
                    module: 'fixed_assets',
                    title: 'Activos Fijos',
                    filePrefix: 'activos-fijos',
                    fields: [
                        'asset_name',
                        'description',
                        'acquisition_value',
                        'acquisition_date',
                        'useful_life_years',
                        'residual_value',
                        'accumulated_depreciation',
                        'status',
                        'disposal_date',
                        'disposal_reason',
                    ],
                    uniqueBy: ['asset_name', 'acquisition_date'],
                    defaults: [
                        'status' => 'active',
                        'residual_value' => 0,
                        'accumulated_depreciation' => 0,
                    ],
                    enumMaps: [
                        'status' => [
                            'active' => 'active',
                            'activo' => 'active',
                            'disposed' => 'disposed',
                            'dado de baja' => 'disposed',
                            'under_maintenance' => 'under_maintenance',
                            'mantenimiento' => 'under_maintenance',
                        ],
                    ],
                    fieldLabels: [
                        'asset_name' => 'Nombre del Activo',
                        'description' => 'Descripcion',
                        'acquisition_value' => 'Valor de Adquisicion',
                        'acquisition_date' => 'Fecha de Adquisicion',
                        'useful_life_years' => 'Vida Util',
                        'residual_value' => 'Valor Residual',
                        'accumulated_depreciation' => 'Depreciacion Acumulada',
                        'status' => 'Estado',
                        'disposal_date' => 'Fecha de Baja',
                        'disposal_reason' => 'Motivo de Baja',
                    ],
                ),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->can('fixed_assets.delete') ?? false)
                        ->modalHeading('Eliminar activos fijos')
                        ->modalDescription('Solo se pueden eliminar activos activos sin depreciacion registrada.')
                        ->modalSubmitActionLabel('Si, eliminar')
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
                                    ->body('Solo se pueden eliminar activos activos sin depreciacion registrada.')
                                    ->persistent()
                                    ->send();

                                $action->halt();
                            }
                        }),
                ]),
            ]);
    }
}
