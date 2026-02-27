<?php

namespace App\Filament\Resources\InventoryProducts\Tables;

use App\Filament\Support\CrudImportExportActions;
use App\Models\InventoryProduct;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InventoryProductsTable
{
    /**
     * Configura la tabla con control de permisos
     *
     * @param Table $table
     * @param array $permissions ['canView' => bool, 'canEdit' => bool, 'canDelete' => bool]
     */
    public static function configure(Table $table, array $permissions = []): Table
    {
        $canView = $permissions['canView'] ?? true;
        $canEdit = $permissions['canEdit'] ?? true;
        $canDelete = $permissions['canDelete'] ?? true;

        $recordActions = [];
        if ($canView) $recordActions[] = ViewAction::make();
        if ($canEdit) $recordActions[] = EditAction::make();

        $bulkActions = [];
        if ($canDelete) {
            $bulkActions[] = DeleteBulkAction::make()
                ->modalHeading('Eliminar productos del inventario')
                ->modalDescription('¿Estás seguro de eliminar los productos seleccionados? Esta acción no se puede deshacer.')
                ->modalSubmitActionLabel('Sí, eliminar')
                ->successNotificationTitle('Productos eliminados correctamente');
        }

        return $table
            ->columns([
                TextColumn::make('inventory.name')
                    ->label('Inventario')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->weight('bold'),

                TextColumn::make('inventory.customer_full_name')
                    ->label('Cliente')
                    ->state(fn ($record) =>
                        $record->inventory->customer
                            ? "{$record->inventory->customer->name} {$record->inventory->customer->first_last_name}"
                            : '-'
                    )
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('Producto')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('stock_initial')
                    ->label('Stock Inicial')
                    ->alignment('center')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('entries')
                    ->label('Entradas')
                    ->alignment('center')
                    ->badge()
                    ->color('success'),

                TextColumn::make('exits')
                    ->label('Salidas')
                    ->alignment('center')
                    ->badge()
                    ->color('danger'),

                TextColumn::make('existence')
                    ->label('Existencia')
                    ->alignment('center')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ?? 0)
                    ->color(fn ($record) => 
                        ($record->stock_initial + $record->entries - $record->exits) < 10 
                            ? 'warning' 
                            : 'success'
                    ),
            ])
            ->filters([
                SelectFilter::make('inventory_id')
                    ->label('Filtrar por Inventario')
                    ->relationship('inventory', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('product_id')
                    ->label('Filtrar por Producto')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('inventory.name', 'asc')
            ->recordActions($recordActions)
            ->toolbarActions([
                ...CrudImportExportActions::make(
                    modelClass: InventoryProduct::class,
                    module: 'inventory_products',
                    title: 'Productos por Inventario',
                    filePrefix: 'inventario-productos',
                    fields: [
                        'inventory_id',
                        'product_id',
                        'stock_initial',
                        'entries',
                        'exits',
                    ],
                    uniqueBy: ['inventory_id', 'product_id'],
                    defaults: [
                        'stock_initial' => 0,
                        'entries' => 0,
                        'exits' => 0,
                    ],
                    requiredFields: ['inventory_id', 'product_id'],
                    fieldLabels: [
                        'inventory.name' => 'Inventario',
                        'product.name' => 'Producto',
                        'stock_initial' => 'Stock Inicial',
                        'entries' => 'Entradas',
                        'exits' => 'Salidas',
                    ],
                    exportFields: [
                        'inventory.name',
                        'product.name',
                        'stock_initial',
                        'entries',
                        'exits',
                    ],
                ),
                BulkActionGroup::make($bulkActions),
            ]);
    }
}