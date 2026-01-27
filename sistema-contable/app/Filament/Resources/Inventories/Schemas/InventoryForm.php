<?php

namespace App\Filament\Resources\Inventories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('customer_id')
                ->relationship('customer', 'name')
                ->label('Cliente')
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('name')
                ->label('Nombre del inventario')
                ->required()
                ->maxLength(255),
        ]);
    }
}
