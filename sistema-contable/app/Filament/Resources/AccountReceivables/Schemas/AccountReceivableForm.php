<?php

namespace App\Filament\Resources\AccountReceivables\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\AccountReceivable;

class AccountReceivableForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();

        $canCreate = $user?->can('account_receivables.create') ?? false;
        $canUpdate = $user?->can('account_receivables.update') ?? false;

        return $schema
            ->components([

                Section::make('Información del Cliente')
                    ->schema([

                        Select::make('customer_id')
                            ->label('Cliente')
                            ->relationship('customer', 'name')
                            ->required()
                            ->disabled(fn ($record) =>
                                (!$canCreate && !$record) ||
                                (!$canUpdate && $record) ||
                                $record?->status === 'paid'
                            )
                            ->validationMessages([
                                'required' => 'El cliente es obligatorio.',
                            ]),

                        TextInput::make('invoice_number')
                            ->label('Número de Factura')
                            ->required()
                            ->maxLength(50)
                            ->rule(fn (?AccountReceivable $record) =>
                                Rule::unique('accounts_receivable', 'invoice_number')
                                    ->ignore($record?->id)
                            )
                            ->disabled(fn ($record) =>
                                (!$canCreate && !$record) ||
                                (!$canUpdate && $record) ||
                                $record?->status === 'paid'
                            )
                            ->validationMessages([
                                'required' => 'La factura es obligatoria.',
                                'max' => 'La factura no puede exceder 50 caracteres.',
                                'unique' => 'Ya existe una cuenta por cobrar con esta factura.',
                            ]),
                    ])
                    ->columns(2),

        
                Section::make('Fechas y Detalle')
                    ->schema([

                        DatePicker::make('issue_date')
                            ->label('Fecha de Emisión')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->disabled(fn ($record) =>
                                (!$canCreate && !$record) ||
                                (!$canUpdate && $record) ||
                                $record?->status === 'paid'
                            )
                            ->validationMessages([
                                'required' => 'La fecha de emisión es obligatoria.',
                            ]),

                        DatePicker::make('due_date')
                            ->label('Fecha de Vencimiento')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->minDate(fn (callable $get) => $get('issue_date'))
                            ->disabled(fn ($record) =>
                                (!$canCreate && !$record) ||
                                (!$canUpdate && $record) ||
                                $record?->status === 'paid'
                            )
                            ->validationMessages([
                                'required' => 'La fecha de vencimiento es obligatoria.',
                            ]),

                        TextInput::make('description')
                            ->label('Descripción')
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn ($record) =>
                                (!$canCreate && !$record) ||
                                (!$canUpdate && $record) ||
                                $record?->status === 'paid'
                            )
                            ->validationMessages([
                                'required' => 'La descripción es obligatoria.',
                                'max' => 'La descripción no puede exceder 255 caracteres.',
                            ]),
                    ])
                    ->columns(2),

                Section::make('Información Financiera')
                    ->schema([

                        TextInput::make('total_amount')
                            ->label('Monto Total')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->gt(0)
                            ->rules(['min:0.01'])
                            ->prefix('CRC')
                            ->disabled(fn ($record) =>
                                (!$canCreate && !$record) ||
                                (!$canUpdate && $record) ||
                                $record?->status === 'paid'
                            )
                            ->validationMessages([
                                'required' => 'El monto total es obligatorio.',
                                'numeric' => 'El monto total debe ser numérico.',
                                'min' => 'El monto total debe ser mayor a cero.',
                                'gt' => 'El monto total debe ser mayor a cero.',
                            ]),

                        TextInput::make('paid_amount')
                            ->label('Monto Pagado')
                            ->numeric()
                            ->prefix('CRC')
                            ->disabled() 
                            ->dehydrated(false)
                            ->helperText('Este campo se actualiza automáticamente mediante los cobros.'),

                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'partial' => 'Parcial',
                                'paid' => 'Pagado',
                            ])
                            ->required()
                            ->disabled(fn ($record) =>
                                !$canUpdate ||
                                $record?->status === 'paid'
                            ),
                    ])
                    ->columns(2),
            ]);
    }
}