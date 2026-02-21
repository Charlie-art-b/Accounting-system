<?php

namespace App\Filament\Resources\Suppliers\Tables;

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
        return $table
        ->defaultSort('nombre_razon_social', 'asc')//orden alfabetico por nombre de proveedor
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
                        'danger' => 'inactivo',
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
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records, DeleteBulkAction $action) {
                            $blockedCount = 0;
                            $blockedReasons = [];

                            foreach ($records as $supplier) {
                                // Verificar cuentas por pagar con saldo pendiente
                                $pendingAccounts = $supplier->cuentasPorPagar()
                                    ->whereIn('status', ['pending', 'partial'])
                                    ->count();

                                if ($pendingAccounts > 0) {
                                    $blockedCount++;
                                    $blockedReasons[] = "{$supplier->nombre_razon_social}: {$pendingAccounts} cuenta(s) por pagar pendiente(s)";
                                    continue;
                                }

                                // Verificar productos
                                $productsCount = $supplier->productos()->count();
                                if ($productsCount > 0) {
                                    $blockedCount++;
                                    $blockedReasons[] = "{$supplier->nombre_razon_social}: {$productsCount} producto(s) asociado(s)";
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
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('¡Proveedor(es) eliminado(s)!')
                                ->body('Los proveedores seleccionados han sido eliminados correctamente.')
                        ),
                ]),
            ]);
    }
}
