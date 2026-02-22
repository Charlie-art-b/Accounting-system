<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Support\CrudImportExportActions;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('supplier.nombre_razon_social')
                    ->label('Proveedor')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                ...CrudImportExportActions::make(
                    modelClass: Product::class,
                    title: 'Productos',
                    filePrefix: 'productos',
                    fields: [
                        'id',
                        'name',
                        'description',
                        'supplier_id',
                    ],
                    uniqueBy: ['name', 'supplier_id'],
                    requiredFields: ['name', 'supplier_id'],
                ),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
