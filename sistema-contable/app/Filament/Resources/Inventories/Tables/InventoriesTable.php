<?php

namespace App\Filament\Resources\Inventories\Tables;

use App\Filament\Support\CrudImportExportActions;
use App\Models\Inventory;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

class InventoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')
                ->label('Inventario')
                ->sortable()
                ->searchable(),
                
            TextColumn::make('customer_full_name')
                ->label('Cliente')
                ->sortable()
                ->state(fn ($record) =>
                        $record->customer
                            ? "{$record->customer->name} {$record->customer->first_last_name} {$record->customer->second_last_name}"
                            : '-'
                ),

            TextColumn::make('inventoryProducts_count')
                ->label('Cantidad de productos')
                ->badge()
                ->color('success')
                ->state(fn ($record) => $record->inventoryProducts()->count()),
        ])

        ->filters([
            SelectFilter::make('customer_id')
                ->label('Cliente')
                ->relationship('customer', 'name')
                ->searchable()
                ->preload(),
        
            Filter::make('with_low_stock')
                ->label('Con stock bajo')
                ->query(fn ($query) => $query->whereHas('inventoryProducts', function ($q) {
                    $q->whereRaw('(stock_initial + entries - exits) < 10');
                })),
            Filter::make('empty_inventory')
                ->label('Inventarios vacíos')
                ->query(fn ($query) => $query->has('inventoryProducts', '=', 0)),
        ])

        ->recordActions([
                ViewAction::make(),
                EditAction::make(),
        ])
        ->toolbarActions([
            ...CrudImportExportActions::make(
                modelClass: Inventory::class,
                title: 'Inventarios',
                filePrefix: 'inventarios',
                fields: [
                    'id',
                    'customer_id',
                    'name',
                ],
                uniqueBy: ['customer_id', 'name'],
            ),
            BulkActionGroup::make([
                DeleteBulkAction::make()
                    ->modalHeading('Eliminar inventarios')
                    ->modalDescription('¿Estás seguro de que deseas eliminar los inventarios seleccionados? Solo se eliminarán inventarios vacíos sin movimientos. Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->successNotificationTitle('Inventario(s) eliminado(s) correctamente')
                    ->before(function ($action, $records) {
                        $blockedInventories = [];

                        foreach ($records as $record) {
                            // Verificar productos con existencias
                            $productsWithStock = $record->inventoryProducts()
                                ->get()
                                ->filter(function ($product) {
                                    $existence = $product->stock_initial + $product->entries - $product->exits;
                                    return $existence > 0;
                                });

                            if ($productsWithStock->count() > 0) {
                                $blockedInventories[] = "• {$record->name}: {$productsWithStock->count()} producto(s) con existencias";
                                continue;
                            }

                            // Verificar productos con movimientos
                            $productsWithMovements = $record->inventoryProducts()
                                ->where(function ($query) {
                                    $query->where('entries', '>', 0)
                                          ->orWhere('exits', '>', 0);
                                })
                                ->count();

                            if ($productsWithMovements > 0) {
                                $blockedInventories[] = "• {$record->name}: {$productsWithMovements} producto(s) con movimientos";
                            }
                        }

                        if (count($blockedInventories) > 0) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('No se pueden eliminar inventarios')
                                ->body('Solo se pueden eliminar inventarios vacíos y sin movimientos registrados.')
                                ->persistent()
                                ->send();

                            $action->halt();
                        }
                    }),
            ]),
        ]);
    }
}
