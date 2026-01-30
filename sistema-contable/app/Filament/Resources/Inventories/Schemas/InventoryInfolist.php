<?php

namespace App\Filament\Resources\Inventories\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Schema;

class InventoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name')
            ->label('Inventario'),

            TextEntry::make('customer_full_name')
            ->label('Cliente')
            ->state(fn ($record) =>
                $record->customer
                    ? "{$record->customer->name} {$record->customer->first_last_name} {$record->customer->second_last_name}"
                    : '-'
            ),

            TextEntry::make('inventoryProducts.product.name')
                ->label('Productos en el inventario')
                ->badge(),

            TextEntry::make('inventoryProducts_count')
                ->label('Cantidad de productos')
                ->badge()
                ->color('success')
                ->state(fn ($record) => $record->inventoryProducts()->count()),


            TextEntry::make('created_at')
                ->label('Creado el')
                ->dateTime(),

            TextEntry::make('updated_at')
                ->label('Última actualización')
                ->dateTime(),
        ]);
    }
}
