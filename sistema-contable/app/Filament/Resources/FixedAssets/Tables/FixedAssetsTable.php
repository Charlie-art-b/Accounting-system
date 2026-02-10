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
                    ->searchable(),
                TextColumn::make('acquisition_value')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('acquisition_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('useful_life_years')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('residual_value')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('accumulated_depreciation')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('net_value')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('disposal_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('disposal_reason')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
