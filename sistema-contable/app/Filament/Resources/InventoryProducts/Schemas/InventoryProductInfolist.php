<?php

namespace App\Filament\Resources\InventoryProducts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InventoryProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información General')
                ->description('Inventario y producto asociado')
                ->columns(2)
                ->schema([
                    TextEntry::make('inventory.name')
                        ->label('Inventario')
                        ->icon('heroicon-o-archive-box')
                        ->badge()
                        ->color('info'),

                    TextEntry::make('product.name')
                        ->label('Producto')
                        ->icon('heroicon-o-cube')
                        ->badge()
                        ->color('success'),
                ]),

            Section::make('Movimientos de Inventario')
                ->description('Cantidades y existencias')
                ->columns(4)
                ->schema([
                    TextEntry::make('stock_initial')
                        ->label('Stock Inicial')
                        ->badge()
                        ->color('gray')
                        ->suffix(' unidades'),

                    TextEntry::make('entries')
                        ->label('Entradas')
                        ->badge()
                        ->color('success')
                        ->suffix(' unidades'),

                    TextEntry::make('exits')
                        ->label('Salidas')
                        ->badge()
                        ->color('danger')
                        ->suffix(' unidades'),

                    TextEntry::make('existence')
                        ->label('Existencia Actual')
                        ->state(fn ($record) => ($record->stock_initial + $record->entries - $record->exits))
                        ->badge()
                        ->color(fn ($record) => 
                            ($record->stock_initial + $record->entries - $record->exits) < 10 
                                ? 'warning' 
                                : 'success'
                        )
                        ->suffix(' unidades')
                        ->weight('bold'),
                ]),

            Section::make('Información del Registro')
                ->description('Metadata')
                ->collapsible()
                ->collapsed()
                ->columns(2)
                ->schema([
                    TextEntry::make('created_at')
                        ->label('Creado en')
                        ->dateTime()
                        ->since(),

                    TextEntry::make('updated_at')
                        ->label('Actualizado en')
                        ->dateTime()
                        ->since(),
                ]),
        ]);
    }
}
