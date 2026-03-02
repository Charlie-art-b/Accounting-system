<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Support\CrudImportExportActions;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

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
                SelectFilter::make('supplier_id')
                    ->label('Proveedor')
                    ->relationship('supplier', 'nombre_razon_social')
                    ->searchable()
                    ->preload(),

                Filter::make('created_at')
                    ->label('Fecha de creación')
                    ->form([
                        DatePicker::make('from')->label('Desde'),
                        DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date)
                            );
                    }),
            ])

            ->recordActions([

                ViewAction::make()
                    ->visible(fn () => Auth::user()?->can('products.view')),

                EditAction::make()
                    ->visible(fn () => Auth::user()?->can('products.update')),

                DeleteAction::make()
                    ->visible(fn () => Auth::user()?->can('products.delete'))
                    ->before(function (Product $record, DeleteAction $action) {

                        $inventoryCount = $record->inventoryProduct()->count();

                        if ($inventoryCount > 0) {
                            Notification::make()
                                ->danger()
                                ->title('No se puede eliminar')
                                ->body('Este producto está asociado a inventario. Elimínalo del inventario primero.')
                                ->send();

                            $action->halt();
                        }
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Producto eliminado')
                            ->body('El producto ha sido eliminado correctamente.')
                    ),
            ])

            ->toolbarActions([

                ...array_filter(CrudImportExportActions::make(
                    modelClass: Product::class,
                    module: 'products',
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
                ), function ($action) {
                    return !in_array($action->getName(), ['import_excel', 'import_pdf']);
                }),

                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->can('products.delete'))
                        ->before(function ($records, DeleteBulkAction $action) {

                            $blockedCount = 0;
                            $blockedReasons = [];

                            foreach ($records as $product) {

                                $inventoryCount = $product->inventoryProduct()->count();

                                if ($inventoryCount > 0) {
                                    $blockedCount++;
                                    $blockedReasons[] =
                                        "{$product->name}: Está en inventario";
                                }
                            }

                            if ($blockedCount > 0) {

                                $reasonsList = implode("\n- ", $blockedReasons);

                                Notification::make()
                                    ->danger()
                                    ->title('NO SE PUEDE ELIMINAR')
                                    ->body("No se pueden eliminar {$blockedCount} producto(s):\n\n- {$reasonsList}")
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