<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Producto')
                    ->description('Datos básicos del producto')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre')
                            ->size('lg')
                            ->weight('bold'),
                    ]),

                Section::make('Descripción')
                    ->description('Información detallada')
                    ->schema([
                        TextEntry::make('description')
                            ->label('Descripción')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Proveedor')
                    ->description('Proveedor asociado')
                    ->schema([
                        TextEntry::make('supplier.nombre_razon_social')
                            ->label('Proveedor')
                            ->placeholder('Sin proveedor')
                            ->badge()
                            ->color('success'),
                    ]),

                Section::make('Información del Registro')
                    ->description('Metadata del registro')
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
