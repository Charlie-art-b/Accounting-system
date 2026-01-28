<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Nombre'),
                TextEntry::make('description')
                    ->label('Descripción')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('supplier.nombre_razon_social')
                    ->label('Proveedor')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label('Creado el')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
