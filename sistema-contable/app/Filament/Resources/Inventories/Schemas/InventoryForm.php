<?php

namespace App\Filament\Resources\Inventories\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class InventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        // Permisos del usuario
        $canView = Auth::user()?->can('inventories.view') ?? false;
        $canEdit = Auth::user()?->can('inventories.update') ?? false;

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
                            ->autocomplete(false)
                            ->disabled(!$canEdit), // Si no tiene permiso de edición, se deshabilita

                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->label('Cliente')
                            ->placeholder('Selecciona un cliente')
                            ->searchable()
                            ->preload()
                            ->helperText('Cliente asociado a este inventario')
                            ->required()
                            ->columnSpanFull()
                            ->disabled(!$canEdit), 
                    ]),
            ]);
    }
}