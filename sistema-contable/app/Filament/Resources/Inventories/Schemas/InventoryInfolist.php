<?php

namespace App\Filament\Resources\Inventories\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Schema;

class InventoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informacion del Inventario')
                ->description('Datos basicos')
                ->columns(2)
                ->schema([
                    TextEntry::make('name')
                        ->label('Nombre del Inventario')
                        ->size('lg')
                        ->weight('bold')
                        ->columnSpanFull(),

                    TextEntry::make('customer_full_name')
                        ->label('Cliente')
                        ->state(fn ($record) =>
                            $record->customer
                                ? "{$record->customer->name} {$record->customer->first_last_name} {$record->customer->second_last_name}"
                                : '-'
                        )
                        ->badge()
                        ->color('success'),

                    TextEntry::make('inventoryProducts_count')
                        ->label('Cantidad de Productos')
                        ->badge()
                        ->color('info')
                        ->state(fn ($record) => $record->inventoryProducts()->count()),
                ]),

            Section::make('Productos en el Inventario')
                ->description('Detalle de existencias')
                ->schema([
                    RepeatableEntry::make('inventoryProducts')
                        ->label('Productos')
                        ->schema([
                            TextEntry::make('product.name')
                                ->label('Producto')
                                ->weight('bold'),
                            
                            TextEntry::make('stock_initial')
                                ->label('Stock Inicial')
                                ->alignment('center')
                                ->badge()
                                ->color('gray'),
                            
                            TextEntry::make('entries')
                                ->label('Entradas')
                                ->alignment('center')
                                ->badge()
                                ->color('success'),
                            
                            TextEntry::make('exits')
                                ->label('Salidas')
                                ->alignment('center')
                                ->badge()
                                ->color('danger'),
                            
                            TextEntry::make('existence')
                                ->label('Existencia')
                                ->alignment('center')
                                ->state(fn ($record) => ($record->stock_initial + $record->entries - $record->exits))
                                ->badge()
                                ->color(fn ($record) => 
                                    ($record->stock_initial + $record->entries - $record->exits) < 10 
                                        ? 'warning' 
                                        : 'success'
                                ),
                        ])
                        ->columns(5)
                        ->columnSpanFull(),
                ]),

            Section::make('Informacion del Registro')
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
