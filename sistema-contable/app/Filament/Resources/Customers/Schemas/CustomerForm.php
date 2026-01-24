<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use App\Models\Supplier;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->minLength(2)
                    ->maxLength(100)
                    ->regex('/^[\p{L}\p{N}\s]+$/u'),

                TextInput::make('first_last_name')
                    ->label('Primer apellido')
                    ->required()
                    ->minLength(2)
                    ->maxLength(50)
                    ->regex('/^[\p{L}\p{N}\s]+$/u'),

                TextInput::make('second_last_name')
                    ->label('Segundo apellido')
                    ->minLength(2)
                    ->maxLength(50)
                    ->regex('/^[\p{L}\p{N}\s]+$/u')
                    ->default(null),

                Select::make('id_type')
                    ->label('Tipo de identificación')
                    ->options([
                        'identification' => 'Cédula',
                        'dimex' => 'DIMEX',
                        'passport' => 'Pasaporte',
                    ])
                    ->default('identification')
                    ->required(),

                TextInput::make('identification')
                    ->label('Identificación')
                    ->required()
                    ->maxLength(20)
                    ->unique(table: 'customers', column: 'identification', ignoreRecord: true)
                    ->regex('/^(\d{1}[-\s]?\d{4}[-\s]?\d{4}|\d{11,12}|[A-Z0-9]{6,12})$/i')
                    ->validationMessages([
                        'unique' => 'Esta identificación ya existe en el sistema.',
                    ]),

                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(table: 'customers', column: 'email', ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'Este correo electrónico ya existe en el sistema.',
                    ]),

                TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->nullable()
                    ->minLength(8)
                    ->maxLength(20)
                    ->default(null)
                    ->regex('/^[0-9()+\-\s]+$/'),

                TextInput::make('address')
                    ->label('Dirección')
                    ->nullable()
                    ->maxLength(355),

                Select::make('customer_type')
                    ->label('Tipo de cliente')
                    ->options([
                        'individual' => 'Persona física',
                        'legal_person' => 'Persona jurídica',
                    ])
                    ->default('individual')
                    ->required(),
                            
                Toggle::make('status')
                    ->label('Estado')
                    ->helperText('Indica si el cliente está activo o inactivo.')
                    ->default(true)
                    ->required(),

                Textarea::make('notes')
                    ->label('Notas')
                    ->placeholder('Escriba información adicional del cliente (opcional).')
                    ->nullable()
                    ->maxLength(2000)
                    ->columnSpanFull(),

                Select::make('suppliers')
                    ->label(__('Asociar proveedores al cliente'))
                    ->multiple()
                    ->relationship('suppliers', 'identificacion')
                    ->getOptionLabelFromRecordUsing(fn(Supplier $record) => "{$record->nombre_razon_social} - {$record->identificacion}")
                    ->searchable()
                    ->preload()
                    ->helperText(__('Seleccione uno o más proveedores para asociar con este cliente')),
            ]);
    }
}
