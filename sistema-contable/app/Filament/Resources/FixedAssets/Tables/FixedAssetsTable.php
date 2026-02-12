<?php

namespace App\Filament\Resources\FixedAssets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                    
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
