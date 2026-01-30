<?php

namespace App\Filament\Resources\Suppliers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class SuppliersTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->defaultSort('nombre_razon_social', 'asc')//orden alfabetico por nombre de proveedor
            ->columns([
                TextColumn::make('tipo_proveedor')
                    ->searchable(),
                TextColumn::make('nombre_razon_social')
                    ->searchable(),
                TextColumn::make('identificacion')
                    ->searchable(),
                TextColumn::make('correo')
                    ->searchable(),
                TextColumn::make('telefono')
                    ->searchable(),
                TextColumn::make('estado')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipo_proveedor')
                    ->label('Tipo de Proveedor')
                    ->options([
                        'persona' => 'Persona Natural',
                        'empresa' => 'Empresa',
                    ]),
                TernaryFilter::make('estado')
                    ->label('Estado')
                    ->trueLabel('Activo')
                    ->falseLabel('Inactivo'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
