<?php

namespace App\Filament\Resources\InventoryProducts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class InventoryProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('inventory.name')
                ->label('Inventario'),

            TextEntry::make('product.name')
                ->label('Producto'),

            TextEntry::make('stock_initial')
                ->label('Stock Inicial'),

            TextEntry::make('entries')
                ->label('Entradas'),

            TextEntry::make('exits')
                ->label('Salidas'),

            TextEntry::make('existence')
                ->label('Existencia'),
        ]);
    }
}
