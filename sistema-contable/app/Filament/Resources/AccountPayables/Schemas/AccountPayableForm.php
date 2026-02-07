<?php

namespace App\Filament\Resources\AccountPayables\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AccountPayableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('supplier_id')
                    ->relationship('supplier', 'nombre_razon_social')
                    ->exists('suppliers', 'id')
                    ->required(),
                TextInput::make('document_number')
                    ->required()
                    ->maxLength(50)
                    ->rules(['string'])
                    ->unique(
                        table: 'accounts_payable',
                        column: 'document_number',
                        ignorable: fn ($record) => $record,
                        modifyRuleUsing: fn ($rule, Get $get) => $rule->where('supplier_id', $get('supplier_id')),
                    ),
                DatePicker::make('issue_date')
                    ->required()
                    ->rules(['date']),
                Select::make('payment_terms')
                    ->options(['cash' => 'Cash', 'credit' => 'Credit'])
                    ->default('credit')
                    ->required()
                    ->rules(['in:cash,credit']),
                TextInput::make('payment_period')
                    ->numeric()
                    ->default(null)
                    ->required(fn ($get) => $get('payment_terms') === 'credit')
                    ->rules(['nullable', 'integer', 'min:1']),
                DatePicker::make('due_date')
                    ->required()
                    ->rules(['date', 'after_or_equal:issue_date']),
                Select::make('type')
                    ->options([
            'invoice' => 'Invoice',
            'receipt' => 'Receipt',
            'debit_note' => 'Debit note',
            'other' => 'Other',
        ])
                    ->default('invoice')
                    ->required()
                    ->rules(['in:invoice,receipt,debit_note,other']),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->rules(['numeric', 'min:0.01']),
                TextInput::make('paid_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->rules(['numeric', 'min:0', 'lte:total_amount'])
                    ->rule(fn ($get) => $get('status') === 'paid' ? 'same:total_amount' : null)
                    ->rule(fn ($get) => $get('status') === 'pending' ? 'max:0' : null),
                DatePicker::make('payment_date')
                    ->required(fn ($get) => $get('status') === 'paid')
                    ->rules(['nullable', 'date', 'after_or_equal:issue_date']),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'partial' => 'Partial', 'paid' => 'Paid', 'voided' => 'Voided'])
                    ->default('pending')
                    ->required()
                    ->rules(['in:pending,partial,paid,voided']),
            ]);
    }
}
