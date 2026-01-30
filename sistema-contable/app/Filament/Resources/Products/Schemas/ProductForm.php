<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->minLength(2)
                    ->maxLength(100)
                    ->regex('/^[\p{L}\p{N}\s]+$/u'),

                Textarea::make('description')
                    ->label('Descripción')
                    ->placeholder('Descripción del producto')
                    ->default(null)
                    ->columnSpanFull()
                    ->maxLength(500),

                Select::make('supplier_id')
                    ->relationship('supplier', 'nombre_razon_social')
                    ->label('Proveedor')
                    //->multiple()
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }
}
