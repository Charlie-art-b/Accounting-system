<?php

namespace App\Filament\Resources\JournalEntries\Schemas;

use App\Models\AccountingAccount;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class JournalEntryForm
{
    public static function configure(Schema $schema, array $permissions = []): Schema
    {
        $canEdit = $permissions['canEdit'] ?? auth()->user()?->can('journal_entries.update') ?? false;
        $canCreate = $permissions['canCreate'] ?? auth()->user()?->can('journal_entries.create') ?? false;
        $readonly = !($canEdit || $canCreate);

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
                        ->helperText('Empresa o entidad a la que pertenece este asiento.')
                        ->disabled($readonly),

                    Select::make('journal_type')
                        ->label('Tipo de asiento')
                        ->required()
                        ->options([
                            'general' => 'General',
                            'adjustment' => 'Ajuste',
                            'closing' => 'Cierre',
                            'reversal' => 'Reverso',
                        ])
                        ->default('general')
                        ->disabled($readonly),

                    TextInput::make('reference')
                        ->label('Referencia (opcional)')
                        ->maxLength(120)
                        ->default(null)
                        ->helperText('Número de factura, recibo o documento relacionado')
                        ->disabled($readonly),

                    TextInput::make('fiscal_period_id')
                        ->label('Periodo fiscal (opcional)')
                        ->numeric()
                        ->default(null)
                        ->helperText('Periodo contable al que pertenece este asiento.')
                        ->disabled($readonly),

                    Textarea::make('description')
                        ->label('Descripción')
                        ->required()
                        ->columnSpanFull()
                        ->helperText('Explique brevemente la operación contable que se está registrando.')
                        ->disabled($readonly),
                ]),

            Section::make('Movimientos contables')
                ->description('El asiento debe quedar balanceado para poder postear.')
                ->columnSpanFull()
                ->columns(1)
                ->schema([
                    Repeater::make('lines')
                        ->live()
                        ->label('Líneas del asiento')
                        ->relationship()
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
                                    $customerId = $get('../../customer_id') ?? $get('customer_id');
                                    if (!$customerId) return [];
                                    return AccountingAccount::query()
                                        ->where('customer_id', $customerId)
                                        ->where('status', 'Activa')
                                        ->orderBy('code')
                                        ->get()
                                        ->mapWithKeys(fn($a) => [$a->id => $a->display])
                                        ->toArray();
                                })
                                ->helperText('Seleccione una cuenta del cliente. Puede buscar por código o nombre.')
                                ->columnSpan(5)
                                ->disabled($readonly),

                            TextInput::make('description')
                                ->label('Detalle')
                                ->maxLength(200)
                                ->columnSpan(3)
                                ->disabled($readonly),

                            TextInput::make('debit')
                                ->label('Débito')
                                ->live(debounce: 300)
                                ->numeric()
                                ->minValue(0)
                                ->inputMode('decimal')
                                ->step('0.01')
                                ->default(0)
                                ->reactive()
                                ->afterStateUpdated(fn($state, callable $set) => $state > 0 ? $set('credit', 0) : null)
                                ->columnSpan(2)
                                ->disabled($readonly),

                            TextInput::make('credit')
                                ->label('Crédito')
                                ->live(debounce: 300)
                                ->numeric()
                                ->minValue(0)
                                ->inputMode('decimal')
                                ->step('0.01')
                                ->default(0)
                                ->reactive()
                                ->afterStateUpdated(fn($state, callable $set) => $state > 0 ? $set('debit', 0) : null)
                                ->columnSpan(2)
                                ->disabled($readonly),
                        ])
                        ->addActionLabel('+ Agregar línea')
                        ->reactive()
                        ->disabled($readonly),

                    Placeholder::make('totals_hint')
                        ->label('Totales')
                        ->content(fn($livewire) => $livewire->totalsText ?? '—'),
                ]),
        ]);
    }
}