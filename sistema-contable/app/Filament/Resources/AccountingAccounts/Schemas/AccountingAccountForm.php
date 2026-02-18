<?php

namespace App\Filament\Resources\AccountingAccounts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AccountingAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('code')
                    ->label('Código')
                    ->required()
                    ->numeric()
                    ->unique(ignoreRecord: true)
                    ->helperText('Ingrese solo números')
                    ->validationMessages([
                        'required' => 'El código es obligatorio',
                        'numeric' => 'Debe ser un número',
                    ]),

                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(100)
                    ->helperText('Nombre de la cuenta contable')
                    ->validationMessages([
                        'required' => 'El nombre es obligatorio',
                    ]),

                Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'Activo' => 'Activo',
                        'Pasivo' => 'Pasivo',
                        'Patrimonio' => 'Patrimonio',
                        'Ingreso' => 'Ingreso',
                        'Gasto' => 'Gasto',
                    ])
                    ->required()
                    ->validationMessages([
                        'required' => 'Seleccione un tipo',
                    ]),

                // 👉 Toggle solo en edición
                Toggle::make('status')
                    ->label('Cuenta activa')
                    //->hiddenOn('create')
                    ->afterStateHydrated(fn ($component, $state) =>
                        $component->state($state === 'Activa')
                    )
                    ->dehydrateStateUsing(fn ($state) =>
                        $state ? 'Activa' : 'Inactiva'
                    ),
            ]);
    }
}
