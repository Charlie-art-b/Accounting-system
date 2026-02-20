<?php

namespace App\Filament\Resources\JournalEntries\Schemas;

use App\Models\AccountingAccount;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;


class JournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información del asiento')
                ->columns(2)
                ->schema([
                    Select::make('customer_id')
                        ->label('Cliente')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->reactive()
                        ->helperText('Empresa o entidad a la que pertenece este asiento.'),

                    Select::make('journal_type')
                        ->label('Tipo de asiento')
                        ->required()
                        ->options([
                            'general' => 'General',
                            'adjustment' => 'Ajuste',
                            'closing' => 'Cierre',
                            'reversal' => 'Reverso',
                        ])
                        ->default('general'),

                    TextInput::make('reference')
                        ->label('Referencia (opcional)')
                        ->maxLength(120)
                        ->default(null)
                        ->helperText('Número de factura, recibo o documento relacionado'),

                        
                        TextInput::make('fiscal_period_id')
                        ->label('Periodo fiscal (opcional)')
                        ->numeric()
                        ->default(null)
                        ->helperText('Periodo contable al que pertenece este asiento.'),
                        
                        Textarea::make('description')
                            ->label('Descripción')
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Explique brevemente la operación contable que se está registrando.'),
                   /* TextInput::make('source_type')
                        ->label('Tipo de origen (opcional)')
                        ->maxLength(120)
                        ->default(null),

                    TextInput::make('source_id')
                        ->label('ID de origen (opcional)')
                        ->numeric()
                        ->default(null),
                    */
                ]),

            Section::make('Movimientos contables')
                ->description('El asiento debe quedar balanceado para poder postear.')
                ->columnSpanFull()
                ->columns(1)
                ->schema([   
                    //Líneas (HasMany)
                    Repeater::make('lines')
                        ->live()
                        ->label('Líneas del asiento')
                        ->relationship() // usa JournalEntry->lines()
                        ->minItems(2)
                        ->defaultItems(2)
                        ->columns(12)
                        ->schema([
                            Select::make('accounting_account_id')
                                ->label('Cuenta')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->options(function ($get) {
                                    
                                    $customerId = $get('../..//customer_id') ?? $get('../../customer_id') ?? $get('customer_id');

                                    if (! $customerId) {
                                        return [];
                                    }

                                    return AccountingAccount::query()
                                        ->where('customer_id', $customerId)
                                        ->where('status', 'Activa')
                                        ->orderBy('code')
                                        ->get()
                                        ->mapWithKeys(fn ($a) => [$a->id => $a->display])
                                        ->toArray();
                                })
                                ->helperText('Seleccione una cuenta del cliente. Puede buscar por código o nombre.')
                                ->columnSpan(5),

                            TextInput::make('description')
                                ->label('Detalle')
                                ->maxLength(200)
                                ->columnSpan(3),

                            TextInput::make('debit')
                                ->label('Débito')
                                ->live(debounce: 300)
                                ->numeric()
                                ->minValue(0)
                                ->inputMode('decimal')
                                ->step('0.01')
                                ->default(0)
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ((float) $state > 0) {
                                        $set('credit', 0);
                                    }
                                })
                                ->columnSpan(2),

                            TextInput::make('credit')
                                ->label('Crédito')
                                ->live(debounce: 300)
                                ->numeric()
                                ->minValue(0)
                                ->inputMode('decimal')
                                ->step('0.01')
                                ->default(0)
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ((float) $state > 0) {
                                        $set('debit', 0);
                                    }
                                })
                                ->columnSpan(2),
                        ])
                        ->addActionLabel('+ Agregar línea')
                        ->reactive(),

                        Placeholder::make('totals_hint')
                            ->label('Totales')
                            ->content(fn ($livewire) => $livewire->totalsText ?? '—'),
                    ])
                ]);

    }
}
