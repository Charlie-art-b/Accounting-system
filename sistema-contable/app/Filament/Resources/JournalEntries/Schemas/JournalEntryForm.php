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


class JournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('customer_id')
                ->label('Cliente')
                ->relationship('customer', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->reactive(),

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
                ->label('Referencia')
                ->maxLength(120)
                ->default(null),

            Textarea::make('description')
                ->label('Descripción')
                ->required()
                ->columnSpanFull(),

            TextInput::make('fiscal_period_id')
                ->label('Periodo fiscal (opcional)')
                ->numeric()
                ->default(null),

            TextInput::make('source_type')
                ->label('Tipo de origen (opcional)')
                ->maxLength(120)
                ->default(null),

            TextInput::make('source_id')
                ->label('ID de origen (opcional)')
                ->numeric()
                ->default(null),

            //Líneas (HasMany)
            Repeater::make('lines')
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
                        ->getSearchResultsUsing(function (string $search, Get $get): array {
                            // subimos al state del entry
                            $customerId = $get('../../customer_id');
                            if (! $customerId) return [];

                            return AccountingAccount::query()
                                ->where('customer_id', $customerId)
                                ->where('status', 'Activa')
                                ->where(function ($q) use ($search) {
                                    $q->where('code', 'like', "%{$search}%")
                                      ->orWhere('name', 'like', "%{$search}%");
                                })
                                ->orderBy('code')
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn ($a) => [$a->id => $a->display])
                                ->toArray();
                        })
                        ->getOptionLabelUsing(fn ($value): ?string => AccountingAccount::find($value)?->display)
                        ->columnSpan(5),

                    TextInput::make('description')
                        ->label('Detalle')
                        ->maxLength(200)
                        ->columnSpan(3),

                    TextInput::make('debit')
                        ->label('Débito')
                        ->numeric()
                        ->minValue(0)
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
                        ->numeric()
                        ->minValue(0)
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
                ->reactive(),

                Placeholder::make('totals_hint')
                    ->label('Totales')
                    ->content(fn ($livewire) => $livewire->totalsText ?? '—'),
        ]);
    }
}
