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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class SuppliersTable
{
    public static function configure(Table $table): Table
    {
        $user = auth()->user();

        $canView = $user?->can('suppliers.view') ?? false;
        $canUpdate = $user?->can('suppliers.update') ?? false;
        $canDelete = $user?->can('suppliers.delete') ?? false;

        return $table
            ->defaultSort('nombre_razon_social', 'asc')
            ->columns([
                TextColumn::make('nombre_razon_social')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('tipo_proveedor')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'persona' => 'Persona',
                        'empresa' => 'Empresa',
                        default => $state,
                    })
                    ->searchable(),

                TextColumn::make('identificacion')
                    ->label('Identificacion')
                    ->searchable()
                    ->fontFamily('mono'),

                TextColumn::make('contacto')
                    ->label('Contacto')
                    ->state(fn (Supplier $record): string => trim((string) $record->correo) . "\n" . trim((string) $record->telefono))
                    ->formatStateUsing(function (string $state): HtmlString {
                        [$email, $phone] = array_pad(explode("\n", $state, 2), 2, '');

                        return new HtmlString(
                            "<div class='leading-tight'><span class='text-xs'>" . e($email) . "</span><br><span class='text-xs fi-text-color-400'>" . e($phone) . '</span></div>'
                        );
                    })
                    ->html()
                    ->searchable(query: function ($query, string $search): void {
                        $query
                            ->where('correo', 'like', "%{$search}%")
                            ->orWhere('telefono', 'like', "%{$search}%");
                    }),

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
                        'danger' => 'inactivo',
                    ])
                    ->searchable(),

                TextColumn::make('customers_count')
                    ->label('Clientes')
                    ->counts('customers')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                    ->label('Tipo de proveedor')
                    ->options([
                        'persona' => 'Persona',
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
                ViewAction::make()->visible(fn () => $canView),
                EditAction::make()->visible(fn () => $canUpdate),
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
                        'nombre_razon_social' => 'Nombre / Razon Social',
                        'identificacion' => 'Identificacion',
                        'correo' => 'Correo Electronico',
                        'telefono' => 'Telefono',
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
                                    $blockedReasons[] = "{$supplier->nombre_razon_social}: {$pendingAccounts} cuenta(s) pendiente(s)";
                                    continue;
                                }

                                $productsCount = $supplier->productos()->count();

                                if ($productsCount > 0) {
                                    $blockedCount++;
                                    $blockedReasons[] = "{$supplier->nombre_razon_social}: {$productsCount} producto(s) asociado(s)";
                                }
                            }

                            if ($blockedCount > 0) {
                                Notification::make()
                                    ->danger()
                                    ->title('No se puede eliminar')
                                    ->body("No se pueden eliminar {$blockedCount} proveedor(es):\n\n- " . implode("\n- ", $blockedReasons))
                                    ->send();

                                $action->halt();
                            }
                        }),
                ]),
            ]);
    }
}

