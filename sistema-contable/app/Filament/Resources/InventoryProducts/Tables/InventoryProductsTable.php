<?php

namespace App\Filament\Resources\InventoryProducts\Tables;

use App\Filament\Support\CrudImportExportActions;
use App\Models\InventoryProduct;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InventoryProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('inventory.name')
                    ->label('Inventario')
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('Producto')
                    ->sortable(),

                TextColumn::make('stock_initial')
                    ->label('Stock Inicial'),

                TextColumn::make('entries')
                    ->label('Entradas'),

                TextColumn::make('exits')
                    ->label('Salidas'),

                TextColumn::make('existence')
                    ->label('Existencia')
                    ->badge(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                ...CrudImportExportActions::make(
                    modelClass: InventoryProduct::class,
                    title: 'Productos por Inventario',
                    filePrefix: 'inventario-productos',
                    fields: [
                        'id',
                        'inventory_id',
                        'product_id',
                        'stock_initial',
                        'entries',
                        'exits',
                    ],
                    uniqueBy: ['inventory_id', 'product_id'],
                    defaults: [
                        'stock_initial' => 0,
                        'entries' => 0,
                        'exits' => 0,
                    ],
                    requiredFields: ['inventory_id', 'product_id'],
                ),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
