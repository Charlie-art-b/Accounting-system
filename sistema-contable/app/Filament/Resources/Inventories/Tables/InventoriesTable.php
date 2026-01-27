<?php

namespace App\Filament\Resources\Inventories\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InventoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')
                ->label('Inventario')
                ->searchable(),

            TextColumn::make('customer.name')
                ->label('Cliente')
                ->sortable(),
        ]);
    }
}
