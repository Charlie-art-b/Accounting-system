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
                    ->relationship('supplier', 'id')
                    ->required(),
                TextInput::make('document_number')
                    ->required(),
                DatePicker::make('issue_date')
                    ->required(),
                Select::make('payment_terms')
                    ->options(['cash' => 'Cash', 'credit' => 'Credit'])
                    ->default('credit')
                    ->required(),
                TextInput::make('payment_period')
                    ->numeric()
                    ->default(null),
                DatePicker::make('due_date')
                    ->required(),
                Select::make('type')
                    ->options([
            'invoice' => 'Invoice',
            'receipt' => 'Receipt',
            'debit_note' => 'Debit note',
            'other' => 'Other',
        ])
                    ->default('invoice')
                    ->required(),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('paid_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                DatePicker::make('payment_date'),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'partial' => 'Partial', 'paid' => 'Paid', 'voided' => 'Voided'])
                    ->default('pending')
                    ->required(),
            ]);
    }
}
