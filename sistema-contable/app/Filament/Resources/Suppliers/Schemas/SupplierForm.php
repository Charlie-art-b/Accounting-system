<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use App\Models\Customer;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        $canUpdate = auth()->user()?->can('suppliers.update') ?? false;
        $canCreate = auth()->user()?->can('suppliers.create') ?? false;

        return $schema
            ->components([

                Section::make('Información del Proveedor')
                    ->description('Datos básicos del proveedor')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        Select::make('tipo_proveedor')
                            ->label('Tipo de Proveedor')
                            ->options([
                                'persona' => 'Persona Natural',
                                'empresa' => 'Empresa',
                            ])
                            ->required()
                            ->default('persona')
                            ->disabled(!$canUpdate && !$canCreate),

                        TextInput::make('nombre_razon_social')
                            ->label('Nombre / Razón Social')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Juan García López o Empresa XYZ S.A.')
                            ->columnSpanFull()
                            ->disabled(!$canUpdate && !$canCreate),
                    ]),

                Section::make('Identificación')
                    ->description('Documento de identidad del proveedor')
                    ->collapsible()
                    ->schema([
                        TextInput::make('identificacion')
                            ->label('Identificación')
                            ->required()
                            ->unique('suppliers', 'identificacion', ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('Ej: 1234567890')
                            ->disabled(!$canUpdate && !$canCreate),
                    ]),

                Section::make('Contacto')
                    ->description('Información de contacto del proveedor')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        TextInput::make('correo')
                            ->label('Correo Electrónico')
                            ->required()
                            ->email()
                            ->unique('suppliers', 'correo', ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('Ej: contacto@proveedor.com')
                            ->disabled(!$canUpdate && !$canCreate),

                        TextInput::make('telefono')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('Ej: +506 8888-8888')
                            ->disabled(!$canUpdate && !$canCreate),
                    ]),

                Section::make('Estado')
                    ->description('Estado del proveedor en el sistema')
                    ->collapsible()
                    ->schema([
                        Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'activo' => 'Activo',
                                'inactivo' => 'Inactivo',
                            ])
                            ->required()
                            ->default('activo')
                            ->disabled(!$canUpdate),
                    ]),

                Section::make('Clientes Asociados')
                    ->description('Clientes vinculados con este proveedor')
                    ->collapsible()
                    ->schema([
                        Select::make('customers')
                            ->label('Clientes Asociados')
                            ->multiple()
                            ->relationship('customers', 'identification')
                            ->getOptionLabelFromRecordUsing(
                                fn (Customer $record) =>
                                "{$record->name} {$record->first_last_name} - {$record->identification}"
                            )
                            ->searchable()
                            ->preload()
                            ->columnSpanFull()
                            ->disabled(!$canUpdate),
                    ]),
            ]);
    }
}