<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                    ->placeholder('Ej: Juan García López o Empresa XYZ S.A.'),
                
                TextInput::make('identificacion')
                    ->label('Identificación')
                    ->required()
                    ->unique('suppliers', 'identificacion', ignoreRecord: true)
                    ->maxLength(50)
                    ->helperText('Cédula, pasaporte o número de identificación')
                    ->placeholder('Ej: 1234567890'),
                
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
                
                Select::make('estado')
                    ->label('Estado')
                    ->options([
                        'activo' => 'Activo',
                        'inactivo' => 'Inactivo',
                    ])
                    ->required()
                    ->default('activo')
                    ->helperText('Estado del proveedor'),

                Select::make('customers')
                    ->label('Clientes Asociados')
                    ->multiple()
                    ->relationship('customers', 'identification')
                    ->getOptionLabelFromRecordUsing(fn(Customer $record) => "{$record->name} {$record->first_last_name} - {$record->identification}")
                    ->searchable()
                    ->preload()
                    ->helperText('Selecciona uno o más clientes para asociarlos con este proveedor'),
            ]);
    }
}
