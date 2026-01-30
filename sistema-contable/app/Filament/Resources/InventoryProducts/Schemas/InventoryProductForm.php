<?php

namespace App\Filament\Resources\InventoryProducts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InventoryProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('inventory_id')
                ->relationship('inventory', 'name')
                ->label('Inventario')
                ->required(),

            Select::make('product_id')
                ->relationship('product', 'name')
                ->label('Producto')
                ->required(),

            TextInput::make('stock_initial')
                ->label('Stock inicial')
                ->numeric()
                ->default(0)
                ->required(),

            TextInput::make('entries')
                ->label('Entradas')
                ->numeric()
                ->default(0),

            TextInput::make('exits')
                ->label('Salidas')
                ->numeric()
                ->default(0),
        ]);
    }
}
