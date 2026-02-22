<?php

namespace App\Filament\Resources\AccountingAccounts\Schemas;

use App\Models\AccountingAccount;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class AccountingAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->preload()
                    ->required(),

                TextInput::make('code')
                    ->label('Código')
                    ->required()
                    ->maxLength(50)
                    ->rule(function ($get, $record) {
                        return Rule::unique('accounting_accounts', 'code')
                            ->where('customer_id', $get('customer_id'))
                            ->ignore($record);
                    })
                    ->helperText('El código debe ser único por cliente'),

                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(100),

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
                    ->reactive(),

                // ✅ NUEVO: Clasificación detallada
                Select::make('classification')
                    ->label('Clasificación')
                    ->options(AccountingAccount::CLASSIFICATIONS)
                    ->searchable()
                    ->nullable(),

                // ✅ NUEVO: Sección del reporte (flujo de efectivo u otros)
                TextInput::make('report_section')
                    ->label('Sección del Reporte')
                    ->maxLength(100)
                    ->nullable(),

                // ✅ NUEVO: Cuenta Padre (Jerarquía)
                Select::make('parent_id')
                    ->label('Cuenta Padre')
                    ->relationship('parent', 'display')
                    ->searchable()
                    ->nullable()
                    ->helperText('Opcional. Para crear estructura jerárquica'),

                // Nivel automático basado en el padre
                TextInput::make('level')
                    ->default(1)
                    ->hidden()
                    ->dehydrateStateUsing(function ($state, $get) {
                        if ($get('parent_id')) {
                            $parent = AccountingAccount::find($get('parent_id'));
                            return $parent ? $parent->level + 1 : 1;
                        }
                        return 1;
                    }),

                Select::make('normal_balance')
                    ->label('Naturaleza')
                    ->options([
                        'debit' => 'Deudora',
                        'credit' => 'Acreedora',
                    ])
                    ->required(),

                Toggle::make('status')
                    ->label('Cuenta activa')
                    ->hiddenOn('create')
                    ->afterStateHydrated(
                        fn ($component, $state) =>
                        $component->state($state === 'Activa')
                    )
                    ->dehydrateStateUsing(
                        fn ($state) =>
                        $state ? 'Activa' : 'Inactiva'
                    ),
            ]);
    }
}