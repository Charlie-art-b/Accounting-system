<?php

namespace App\Filament\Resources\AccountReceivables\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class AccountReceivableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informacion del Cliente')
                    ->schema([
                        Select::make('customer_id')
                            ->label('Clientes')
                            ->relationship('customer', 'name')
                            ->required()
                            ->validationMessages([
                                'required' => 'El cliente es obligatorio.',
                            ]),
                        TextInput::make('invoice_number')
                            ->label('Numero de Factura')
                            ->required()
                            ->maxLength(50)
                            ->rule(fn (? \App\Models\AccountReceivable $record) =>
                                Rule::unique('accounts_receivable', 'invoice_number')
                                    ->ignore($record?->id)
                            )
                            ->validationMessages([
                                'required' => 'La factura es obligatoria.',
                                'max' => 'La factura no puede exceder 50 caracteres.',
                                'unique' => 'Ya existe una cuenta por cobrar con esta factura para el cliente seleccionado.',
                            ]),
                    ])
                    ->columns(2),

                Section::make('Fechas y Detalle')
                    ->schema([
                        DatePicker::make('issue_date')
                            ->label('Fecha de Emision')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->validationMessages([
                                'required' => 'La fecha de emisión es obligatoria.',
                                'date' => 'La fecha de emisión no es válida.',
                            ]),
                        DatePicker::make('due_date')
                            ->label('Fecha de Vencimiento')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->minDate(fn (callable $get) => $get('issue_date'))
                            ->validationMessages([
                                'required' => 'La fecha de vencimiento es obligatoria.',
                                'minDate' => 'La fecha de vencimiento debe ser igual o posterior a la fecha de emisión.',
                            ]),
                        TextInput::make('description')
                            ->label('Descripcion')
                            ->required()
                            ->maxLength(255)
                            ->validationMessages([
                                'required' => 'La descripción es obligatoria.',
                                'max' => 'La descripción no puede exceder 255 caracteres.',
                            ]),
                    ])
                    ->columns(2),

                Section::make('Informacion Financiera')
                    ->schema([
                        TextInput::make('total_amount')
                            ->label('Monto Total')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->gt(0)
                            ->rules(['min:0.01'])
                            ->prefix('CRC')
                            ->validationMessages([
                                'required' => 'El monto total es obligatorio.',
                                'numeric' => 'El monto total debe ser numérico.',
                                'min' => 'El monto total debe ser mayor a cero.',
                                'gt' => 'El monto total debe ser mayor a cero.',
                            ]),
                    ])
                    ->columns(2),
            ]);
    }
}
