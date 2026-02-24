<?php

namespace App\Filament\Resources\Inventories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Información del Inventario')
                    ->description('Datos básicos del inventario')
                    ->icon('heroicon-o-archive-box')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del inventario')
                            ->placeholder('Ej: Inventario General 2026')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->autocomplete(false),

                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->label('Cliente')
                            ->placeholder('Selecciona un cliente')
                            ->searchable()
                            ->preload()
                            ->helperText('Cliente asociado a este inventario')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
