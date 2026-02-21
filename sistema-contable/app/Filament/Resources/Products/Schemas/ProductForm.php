<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Schemas\Components\Section;
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
                Section::make('Información del Producto')
                    ->description('Datos básicos del producto')
                    ->collapsible()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->minLength(2)
                            ->maxLength(100)
                            ->regex('/^[\p{L}\p{N}\s]+$/u')
                            ->helperText('Nombre del producto')
                            ->placeholder('Ej: Laptop HP Pavilion'),
                    ]),

                Section::make('Descripción')
                    ->description('Información detallada del producto')
                    ->collapsible()
                    ->schema([
                        Textarea::make('description')
                            ->label('Descripción')
                            ->placeholder('Descripción del producto (opcional)')
                            ->rows(4)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),

                Section::make('Proveedor')
                    ->description('Proveedor asociado al producto')
                    ->collapsible()
                    ->schema([
                        Select::make('supplier_id')
                            ->relationship('supplier', 'nombre_razon_social')
                            ->label('Proveedor')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Selecciona el proveedor del producto'),
                    ]),
            ]);
    }
}
