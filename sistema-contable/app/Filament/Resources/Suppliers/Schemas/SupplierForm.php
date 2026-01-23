<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tipo_proveedor')
                    ->required()
                    ->default('persona'),
                TextInput::make('nombre_razon_social')
                    ->required(),
                TextInput::make('identificacion')
                    ->required(),
                TextInput::make('correo')
                    ->required(),
                TextInput::make('telefono')
                    ->tel()
                    ->default(null),
                TextInput::make('estado')
                    ->required()
                    ->default('activo'),
            ]);
    }
}
