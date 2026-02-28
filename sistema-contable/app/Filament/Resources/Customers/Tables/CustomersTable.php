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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')
            ->columns([
                TextColumn::make('cliente')
                    ->label('Cliente')
                    ->state(function (Customer $record): string {
                        $fullName = trim($record->name . ' ' . $record->first_last_name);
                        $secondLastName = trim((string) $record->second_last_name);

                        return $secondLastName !== ''
                            ? "{$fullName}\n{$secondLastName}"
                            : $fullName;
                    })
                    ->formatStateUsing(function (string $state): HtmlString {
                        [$main, $secondary] = array_pad(explode("\n", $state, 2), 2, '');

                        $main = e($main);
                        $secondary = e($secondary);

                        if ($secondary === '') {
                            return new HtmlString("<span class='font-medium'>{$main}</span>");
                        }

                        return new HtmlString(
                            "<div class='leading-tight'><span class='font-medium'>{$main}</span><br><span class='text-xs fi-text-color-400'>{$secondary}</span></div>"
                        );
                    })
                    ->html()
                    ->searchable(query: function ($query, string $search): void {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('first_last_name', 'like', "%{$search}%")
                            ->orWhere('second_last_name', 'like', "%{$search}%");
                    }),

                TextColumn::make('id_type')
                    ->label('Tipo ID')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'identification' => 'Cedula',
                        'dimex' => 'DIMEX',
                        'passport' => 'Pasaporte',
                        default => $state,
                    })
                    ->searchable(),

                TextColumn::make('identification')
                    ->label('Identificacion')
                    ->searchable()
                    ->fontFamily('mono'),

                TextColumn::make('contacto')
                    ->label('Contacto')
                    ->state(fn (Customer $record): string => trim((string) $record->email) . "\n" . trim((string) $record->phone))
                    ->formatStateUsing(function (string $state): HtmlString {
                        [$email, $phone] = array_pad(explode("\n", $state, 2), 2, '');

                        $email = e($email);
                        $phone = e($phone);

                        return new HtmlString(
                            "<div class='leading-tight'><span class='text-xs'>{$email}</span><br><span class='text-xs fi-text-color-400'>{$phone}</span></div>"
                        );
                    })
                    ->html()
                    ->searchable(query: function ($query, string $search): void {
                        $query
                            ->where('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    }),

                TextColumn::make('address')
                    ->label('Ubicacion')
                    ->searchable()
                    ->wrap()
                    ->limit(34),

                TextColumn::make('customer_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'individual' => 'Persona fisica',
                        'legal_person' => 'Persona juridica',
                        default => $state,
                    }),

                TextColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn ($state) => $state ? 'Activo' : 'Inactivo')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger'),

                TextColumn::make('suppliers_count')
                    ->label('Proveedores')
                    ->counts('suppliers')
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
                SelectFilter::make('customer_type')
                    ->label('Tipo de cliente')
                    ->options([
                        'individual' => 'Persona fisica',
                        'legal_person' => 'Persona juridica',
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
                                ->title('No se puede eliminar')
                                ->body("Este cliente tiene {$pendingAccounts} cuenta(s) por cobrar pendiente(s).")
                                ->send();

                            $action->halt();
                        }
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Cliente eliminado')
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
                        'id_type' => 'Tipo de Identificacion',
                        'identification' => 'Identificacion',
                        'email' => 'Correo Electronico',
                        'phone' => 'Telefono',
                        'address' => 'Direccion',
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
                                $reasonsList = implode("\n- ", $blockedReasons);

                                Notification::make()
                                    ->danger()
                                    ->title('No se puede eliminar')
                                    ->body("No se pueden eliminar {$blockedCount} cliente(s):\n\n- {$reasonsList}")
                                    ->send();

                                $action->halt();
                            }
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Clientes eliminados')
                                ->body('Los clientes seleccionados han sido eliminados correctamente.')
                        ),
                ]),
            ]);
    }
}

