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
                            ->helperText('Selecciona el tipo de proveedor'),
                        
                        TextInput::make('nombre_razon_social')
                            ->label('Nombre / Razón Social')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Nombre completo o razón social del proveedor')
                            ->placeholder('Ej: Juan García López o Empresa XYZ S.A.')
                            ->columnSpanFull(),
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
                            ->helperText('Cédula, pasaporte o número de identificación')
                            ->placeholder('Ej: 1234567890'),
                    ]),

                Section::make('Contacto')
                    ->description('Información de contacto del proveedor')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        TextInput::make('correo')
                            ->label('Correo Electrónico')
                            ->required()
                            ->email('El correo debe ser válido')
                            ->unique('suppliers', 'correo', ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Correo de contacto del proveedor')
                            ->placeholder('Ej: contacto@proveedor.com'),
                        
                        TextInput::make('telefono')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(20)
                            ->helperText('Número de teléfono del proveedor (opcional)')
                            ->placeholder('Ej: +34 123 456789'),
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
                            ->helperText('Estado del proveedor'),
                    ]),

                Section::make('Clientes Asociados')
                    ->description('Clientes vinculados con este proveedor')
                    ->collapsible()
                    ->schema([
                        Select::make('customers')
                            ->label('Clientes Asociados')
                            ->multiple()
                            ->relationship('customers', 'identification')
                            ->getOptionLabelFromRecordUsing(fn(Customer $record) => "{$record->name} {$record->first_last_name} - {$record->identification}")
                            ->searchable()
                            ->preload()
                            ->helperText('Selecciona uno o más clientes para asociarlos con este proveedor')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
