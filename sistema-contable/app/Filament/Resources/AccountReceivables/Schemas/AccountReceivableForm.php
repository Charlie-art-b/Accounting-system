<?php

namespace App\Filament\Resources\AccountReceivables\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AccountReceivableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required()
                    ->validationMessages([
                        'required' => 'El cliente es obligatorio.',
                    ]),
                TextInput::make('invoice_number')
                    ->required()
                    ->maxLength(50)
                    ->rule(fn (callable $get) =>
                        Rule::unique('accounts_receivable', 'invoice_number')
                            ->where(fn ($q) => $q->where('customer_id', $get('customer_id')))
                    )
                    ->validationMessages([
                        'required' => 'La factura es obligatoria.',
                        'max' => 'La factura no puede exceder 50 caracteres.',
                        'unique' => 'Ya existe una cuenta por cobrar con esta factura para el cliente seleccionado.',
                    ]),
                DatePicker::make('issue_date')
                    ->required()
                    ->validationMessages([
                        'required' => 'La fecha de emisión es obligatoria.',
                        'date' => 'La fecha de emisión no es válida.',
                    ]),
                DatePicker::make('due_date')
                    ->required()
                    ->minDate(fn (callable $get) => $get('issue_date'))
                    ->validationMessages([
                        'required' => 'La fecha de vencimiento es obligatoria.',
                        'minDate' => 'La fecha de vencimiento debe ser igual o posterior a la fecha de emisión.',
                    ]),
                TextInput::make('description')
                    ->required()
                    ->maxLength(255)
                    ->validationMessages([
                        'required' => 'La descripción es obligatoria.',
                        'max' => 'La descripción no puede exceder 255 caracteres.',
                    ]),
                TextInput::make('total_amount')
                    ->required()->numeric()
                    ->gt(0)
                    ->validationMessages([
                        'required' => 'El monto total es obligatorio.',
                        'numeric' => 'El monto total debe ser numérico.',
                        'gt' => 'El monto total debe ser mayor a cero.',
                    ]),
                TextInput::make('paid_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'partial' => 'Partial', 'paid' => 'Paid'])
                    ->default('pending')
                    ->required(),
            ]);
    }
}
