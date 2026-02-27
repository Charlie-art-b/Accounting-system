<?php

namespace App\Filament\Resources\Inventories\Tables;

use App\Filament\Support\CrudImportExportActions;
use App\Models\Inventory;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;

class InventoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
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
                ViewAction::make()
                    ->visible(fn () => Auth::user()?->can('inventories.view')),

                EditAction::make()
                    ->visible(fn () => Auth::user()?->can('inventories.update')),

                DeleteAction::make()
                    ->visible(fn () => Auth::user()?->can('inventories.delete'))
                    ->before(function ($record, DeleteAction $action) {
                        $productsWithStock = $record->inventoryProducts()
                            ->get()
                            ->filter(fn ($product) => ($product->stock_initial + $product->entries - $product->exits) > 0);

                        $productsWithMovements = $record->inventoryProducts()
                            ->where(function ($query) {
                                $query->where('entries', '>', 0)
                                      ->orWhere('exits', '>', 0);
                            })
                            ->count();

                        if ($productsWithStock->count() > 0 || $productsWithMovements > 0) {
                            $message = "No se puede eliminar este inventario:";
                            if ($productsWithStock->count() > 0) {
                                $message .= " {$productsWithStock->count()} producto(s) con existencias;";
                            }
                            if ($productsWithMovements > 0) {
                                $message .= " {$productsWithMovements} producto(s) con movimientos;";
                            }

                            Notification::make()
                                ->danger()
                                ->title('Inventario bloqueado')
                                ->body($message)
                                ->send();

                            $action->halt();
                        }
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('¡Inventario eliminado!')
                            ->body('El inventario ha sido eliminado correctamente.')
                    ),
            ])
            ->toolbarActions([
                ...CrudImportExportActions::make(
                    modelClass: Inventory::class,
                    module: 'inventories',
                    title: 'Inventarios',
                    filePrefix: 'inventarios',
                    fields: [
                        'customer_id',
                        'name',
                    ],
                    uniqueBy: ['customer_id', 'name'],
                    fieldLabels: [
                        'customer.name' => 'Cliente',
                        'name' => 'Nombre del Inventario',
                    ],
                    exportFields: [
                        'customer.name',
                        'name',
                    ],
                ),

                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Eliminar inventarios')
                        ->modalDescription('Solo se eliminarán inventarios vacíos y sin movimientos. Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar')
                        ->successNotificationTitle('Inventario(s) eliminado(s) correctamente')
                        ->before(function ($records, DeleteBulkAction $action) {
                            $blockedInventories = [];

                            foreach ($records as $record) {
                                $productsWithStock = $record->inventoryProducts()
                                    ->get()
                                    ->filter(fn ($product) => ($product->stock_initial + $product->entries - $product->exits) > 0);

                                $productsWithMovements = $record->inventoryProducts()
                                    ->where(function ($query) {
                                        $query->where('entries', '>', 0)
                                              ->orWhere('exits', '>', 0);
                                    })
                                    ->count();

                                if ($productsWithStock->count() > 0 || $productsWithMovements > 0) {
                                    $blockedInventories[] = "• {$record->name}: " .
                                        ($productsWithStock->count() > 0 ? "{$productsWithStock->count()} producto(s) con existencias; " : '') .
                                        ($productsWithMovements > 0 ? "{$productsWithMovements} producto(s) con movimientos" : '');
                                }
                            }

                            if (count($blockedInventories) > 0) {
                                Notification::make()
                                    ->danger()
                                    ->title('No se pueden eliminar inventarios')
                                    ->body(implode("\n", $blockedInventories))
                                    ->send();

                                $action->halt();
                            }
                        }),
                ]),
            ]);
    }
}