<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Filament\Support\CrudImportExportActions;
use App\Models\Customer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Support\Facades\Auth;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')
            ->columns([

                TextColumn::make('name')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('first_last_name')
                    ->label('Primer apellido')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('second_last_name')
                    ->label('Segundo apellido')
                    ->searchable(),

                TextColumn::make('id_type')
                    ->label('Tipo de identificación')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'identification' => 'Cédula',
                        'dimex' => 'DIMEX',
                        'passport' => 'Pasaporte',
                        default => $state,
                    })
                    ->searchable(),

                TextColumn::make('identification')
                    ->label('Identificación')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),

                TextColumn::make('address')
                    ->label('Dirección')
                    ->searchable(),

                TextColumn::make('customer_type')
                    ->label('Tipo de cliente')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'individual' => 'Persona física',
                        'legal_person' => 'Persona jurídica',
                        default => $state,
                    }),

                IconColumn::make('status')
                    ->label('Estado')
                    ->boolean(),

                TextColumn::make('suppliers_count')
                    ->label('Proveedores')
                    ->counts('suppliers')
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

                SelectFilter::make('customer_type')
                    ->label('Tipo de cliente')
                    ->options([
                        'individual' => 'Persona física',
                        'legal_person' => 'Persona jurídica',
                    ]),

                TernaryFilter::make('status')
                    ->label('Estado')
                    ->trueLabel('Activo')
                    ->falseLabel('Inactivo'),

                SelectFilter::make('suppliers')
                    ->relationship('suppliers', 'nombre_razon_social')
                    ->label('Proveedor'),
            ])

            ->recordActions([

                ViewAction::make()
                    ->visible(fn () => Auth::user()?->can('customers.view')),

                EditAction::make()
                    ->visible(fn () => Auth::user()?->can('customers.update')),

                DeleteAction::make()
                    ->visible(fn () => Auth::user()?->can('customers.delete'))
                    ->before(function ($record, DeleteAction $action) {

                        $pendingAccounts = $record->accountReceivables()
                            ->whereIn('status', ['pending', 'partial'])
                            ->count();

                        if ($pendingAccounts > 0) {
                            Notification::make()
                                ->danger()
                                ->title('NO SE PUEDE ELIMINAR')
                                ->body("Este cliente tiene {$pendingAccounts} cuenta(s) por cobrar pendiente(s).")
                                ->send();

                            $action->halt();
                        }
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('¡Cliente eliminado!')
                            ->body('El cliente ha sido eliminado correctamente.')
                    ),
            ])

            ->toolbarActions([

                ...CrudImportExportActions::make(
                    modelClass: Customer::class,
                    module: 'customers',
                    title: 'Clientes',
                    filePrefix: 'clientes',
                    fields: [
                        'name',
                        'first_last_name',
                        'second_last_name',
                        'id_type',
                        'identification',
                        'email',
                        'phone',
                        'address',
                        'customer_type',
                        'status',
                        'notes',
                    ],
                    uniqueBy: ['identification'],
                    defaults: ['status' => true],
                    enumMaps: [
                        'customer_type' => [
                            'persona fisica' => 'individual',
                            'individual' => 'individual',
                            'persona juridica' => 'legal_person',
                            'legal_person' => 'legal_person',
                        ],
                    ],
                    requiredFields: ['name', 'identification'],
                    fieldLabels: [
                        'name' => 'Nombre',
                        'first_last_name' => 'Primer Apellido',
                        'second_last_name' => 'Segundo Apellido',
                        'id_type' => 'Tipo de Identificación',
                        'identification' => 'Identificación',
                        'email' => 'Correo Electrónico',
                        'phone' => 'Teléfono',
                        'address' => 'Dirección',
                        'customer_type' => 'Tipo de Cliente',
                        'status' => 'Estado',
                        'notes' => 'Notas',
                    ],
                ),

                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->can('customers.delete'))
                        ->before(function ($records, DeleteBulkAction $action) {

                            $blockedCount = 0;
                            $blockedReasons = [];

                            foreach ($records as $customer) {

                                $pendingAccounts = $customer->accountReceivables()
                                    ->whereIn('status', ['pending', 'partial'])
                                    ->count();

                                if ($pendingAccounts > 0) {
                                    $blockedCount++;
                                    $blockedReasons[] =
                                        "{$customer->name} {$customer->first_last_name}: {$pendingAccounts} cuenta(s) pendiente(s)";
                                }
                            }

                            if ($blockedCount > 0) {

                                $reasonsList = implode("\n• ", $blockedReasons);

                                Notification::make()
                                    ->danger()
                                    ->title('NO SE PUEDE ELIMINAR')
                                    ->body("No se pueden eliminar {$blockedCount} cliente(s):\n\n• {$reasonsList}")
                                    ->send();

                                $action->halt();
                            }
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('¡Clientes eliminados!')
                                ->body('Los clientes seleccionados han sido eliminados correctamente.')
                        ),
                ]),
            ]);
    }
}