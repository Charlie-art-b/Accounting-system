<?php

namespace App\Filament\Resources\InventoryProducts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InventoryProductForm
{

    public static function configure(Schema $schema, array $permissions = []): Schema
    {
        $canCreate = $permissions['canCreate'] ?? true;
        $canEdit = $permissions['canEdit'] ?? true;
        $canDelete = $permissions['canDelete'] ?? true;

        $isFromQuickAdd = request()->has('inventory_id');

        return $schema
            ->columns(1)
            ->components([
                Section::make('Identificación')
                    ->description('Selecciona el inventario y producto')
                    ->icon('heroicon-o-tag')
                    ->columns(2)
                    ->schema([
                        Select::make('inventory_id')
                            ->relationship('inventory', 'name')
                            ->label('Inventario')
                            ->placeholder($isFromQuickAdd ? 'Pre-seleccionado' : 'Selecciona un inventario')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled($isFromQuickAdd)
                            ->dehydrated(!$isFromQuickAdd),

                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->label('Producto')
                            ->placeholder('Selecciona un producto')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),

                Section::make('Cantidades')
                    ->description('Registro de stock, entradas y salidas')
                    ->icon('heroicon-o-calculator')
                    ->columns(3)
                    ->schema([
                        TextInput::make('stock_initial')
                            ->label('Stock Inicial')
                            ->placeholder('0')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->suffix('unidades'),

                        TextInput::make('entries')
                            ->label('Entradas')
                            ->placeholder('0')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->dehydrateStateUsing(fn ($state) => $state ?? 0)
                            ->suffix('unidades')
                            ->helperText('Productos que ingresan'),

                        TextInput::make('exits')
                            ->label('Salidas')
                            ->placeholder('0')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->dehydrateStateUsing(fn ($state) => $state ?? 0)
                            ->suffix('unidades')
                            ->helperText('Productos que salen'),
                    ]),
            ])
            ->actions([
                $canCreate ? Action::make('create')->label('Crear') : null,
                $canEdit ? Action::make('save')->label('Guardar') : null,
                $canDelete ? Action::make('delete')->label('Eliminar')->requiresConfirmation() : null,
            ])
            ->compact();
    }
}