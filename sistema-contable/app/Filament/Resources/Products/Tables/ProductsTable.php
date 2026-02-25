<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Support\CrudImportExportActions;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
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
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('supplier.nombre_razon_social')
                    ->label('Proveedor')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('created_at')
                    ->label('Creado en')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado en')
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
                        'name',
                        'description',
                        'supplier_id',
                    ],
                    uniqueBy: ['name', 'supplier_id'],
                    requiredFields: ['name', 'supplier_id'],
                    fieldLabels: [
                        'name' => 'Nombre',
                        'description' => 'Descripción',
                        'supplier.nombre_razon_social' => 'Proveedor',
                    ],
                    exportFields: [
                        'name',
                        'description',
                        'supplier.nombre_razon_social',
                    ],
                ),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records, DeleteBulkAction $action) {
                            $blockedCount = 0;
                            $blockedReasons = [];

                            foreach ($records as $product) {
                                $inventoryCount = $product->inventoryProduct()->count();

                                if ($inventoryCount > 0) {
                                    $blockedCount++;
                                    $blockedReasons[] = "{$product->name}: Está en inventario";
                                }
                            }

                            if ($blockedCount > 0) {
                                $reasonsList = implode("\n• ", $blockedReasons);
                                Notification::make()
                                    ->danger()
                                    ->title('NO SE PUEDE ELIMINAR')
                                    ->body("No se pueden eliminar {$blockedCount} producto(s):\n\n• {$reasonsList}\n\nElimínalos del inventario primero.")
                                    ->send();

                                $action->halt();
                            }
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('¡Producto(s) eliminado(s)!')
                                ->body('Los productos seleccionados han sido eliminados correctamente.')
                        ),
                ]),
            ]);
    }
}
