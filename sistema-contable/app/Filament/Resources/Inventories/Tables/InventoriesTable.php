<?php

namespace App\Filament\Resources\Inventories\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;

class InventoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')
                ->label('Inventario')
                ->sortable()
                ->searchable(),
                
            TextColumn::make('customer_full_name')
                ->label('Cliente')
                ->sortable()
                ->state(fn ($record) =>
                        $record->customer
                            ? "{$record->customer->name} {$record->customer->first_last_name} {$record->customer->second_last_name}"
                            : '-'
                ),

            TextColumn::make('inventoryProducts_count')
                ->label('Cantidad de productos')
                ->badge()
                ->color('success')
                ->state(fn ($record) => $record->inventoryProducts()->count()),
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
