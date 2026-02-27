<?php

namespace App\Filament\Resources\Suppliers\Tables;

use App\Filament\Support\CrudImportExportActions;
use App\Models\Supplier;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class SuppliersTable
{
    public static function configure(Table $table): Table
    {
        $user = auth()->user();

        $canView   = $user?->can('suppliers.view') ?? false;
        $canUpdate = $user?->can('suppliers.update') ?? false;
        $canDelete = $user?->can('suppliers.delete') ?? false;
        $canCreate = $user?->can('suppliers.create') ?? false;

        return $table
            ->defaultSort('nombre_razon_social', 'asc')

            ->columns([
                TextColumn::make('tipo_proveedor')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'persona' => 'Persona Natural',
                        'empresa' => 'Empresa',
                        default => $state,
                    })
                    ->searchable(),

                TextColumn::make('nombre_razon_social')
                    ->label('Nombre / Razón Social')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('identificacion')
                    ->label('Identificación')
                    ->searchable(),

                TextColumn::make('correo')
                    ->label('Correo Electrónico')
                    ->searchable(),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'activo' => 'Activo',
                        'inactivo' => 'Inactivo',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'activo',
                        'danger'  => 'inactivo',
                    ])
                    ->searchable(),

                TextColumn::make('customers_count')
                    ->label('Clientes')
                    ->counts('customers')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->toggleable(),

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
                SelectFilter::make('tipo_proveedor')
                    ->label('Tipo de Proveedor')
                    ->options([
                        'persona' => 'Persona Natural',
                        'empresa' => 'Empresa',
                    ]),

                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'activo' => 'Activo',
                        'inactivo' => 'Inactivo',
                    ]),

                SelectFilter::make('customers')
                    ->relationship('customers', 'name')
                    ->label('Cliente'),
            ])

            ->recordActions([
                ViewAction::make()
                    ->visible(fn () => $canView),

                EditAction::make()
                    ->visible(fn () => $canUpdate),
            ])

          ->toolbarActions([

    ...CrudImportExportActions::make(
        modelClass: Supplier::class,
        module: 'suppliers',
        title: 'Proveedores',
        filePrefix: 'proveedores',
        fields: [
            'tipo_proveedor',
            'nombre_razon_social',
            'identificacion',
            'correo',
            'telefono',
            'estado',
        ],
        uniqueBy: ['identificacion'],
        defaults: ['estado' => 'activo'],
        enumMaps: [
            'tipo_proveedor' => [
                'persona' => 'persona',
                'persona natural' => 'persona',
                'empresa' => 'empresa',
            ],
            'estado' => [
                'activo' => 'activo',
                'inactivo' => 'inactivo',
            ],
        ],
        requiredFields: ['nombre_razon_social', 'identificacion'],
        fieldLabels: [
            'tipo_proveedor' => 'Tipo de Proveedor',
            'nombre_razon_social' => 'Nombre / Razón Social',
            'identificacion' => 'Identificación',
            'correo' => 'Correo Electrónico',
            'telefono' => 'Teléfono',
            'estado' => 'Estado',
        ],
    ),


                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => $canDelete)
                        ->before(function ($records, DeleteBulkAction $action) {

                            $blockedCount = 0;
                            $blockedReasons = [];

                            foreach ($records as $supplier) {

                                $pendingAccounts = $supplier->cuentasPorPagar()
                                    ->whereIn('status', ['pending', 'partial'])
                                    ->count();

                                if ($pendingAccounts > 0) {
                                    $blockedCount++;
                                    $blockedReasons[] =
                                        "{$supplier->nombre_razon_social}: {$pendingAccounts} cuenta(s) pendiente(s)";
                                    continue;
                                }

                                $productsCount = $supplier->productos()->count();

                                if ($productsCount > 0) {
                                    $blockedCount++;
                                    $blockedReasons[] =
                                        "{$supplier->nombre_razon_social}: {$productsCount} producto(s) asociado(s)";
                                }
                            }

                            if ($blockedCount > 0) {
                                $reasonsList = implode("\n• ", $blockedReasons);

                                Notification::make()
                                    ->danger()
                                    ->title('NO SE PUEDE ELIMINAR')
                                    ->body("No se pueden eliminar {$blockedCount} proveedor(es):\n\n• {$reasonsList}")
                                    ->send();

                                $action->halt();
                            }
                        }),
                ]),
            ]);
    }
}