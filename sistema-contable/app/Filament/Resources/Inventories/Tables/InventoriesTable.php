<?php

namespace App\Filament\Resources\Inventories\Tables;

use App\Filament\Support\CrudImportExportActions;
use App\Models\Inventory;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

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
            SelectFilter::make('customer_id')
                ->label('Cliente')
                ->relationship('customer', 'name')
                ->searchable()
                ->preload(),
        
            Filter::make('with_low_stock')
                ->label('Con stock bajo')
                ->query(fn ($query) => $query->whereHas('inventoryProducts', function ($q) {
                    $q->whereRaw('(stock_initial + entries - exits) < 10');
                })),
            Filter::make('empty_inventory')
                ->label('Inventarios vacíos')
                ->query(fn ($query) => $query->has('inventoryProducts', '=', 0)),
        ])

        ->recordActions([
                ViewAction::make(),
                EditAction::make(),
        ])
        ->toolbarActions([
            ...CrudImportExportActions::make(
                modelClass: Inventory::class,
                title: 'Inventarios',
                filePrefix: 'inventarios',
                fields: [
                    'id',
                    'customer_id',
                    'name',
                ],
                uniqueBy: ['customer_id', 'name'],
            ),
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ]);
    }
}
