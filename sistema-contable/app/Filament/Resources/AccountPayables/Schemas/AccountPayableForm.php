<?php

namespace App\Filament\Resources\AccountPayables\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccountPayableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Proveedor y Documento')
                    ->schema([
                        Select::make('supplier_id')
                            ->label('Proveedor')
                            ->relationship('supplier', 'nombre_razon_social')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->columnSpan(2),
                        
                        TextInput::make('document_number')
                            ->label('Número de Documento')
                            ->required()
                            ->maxLength(255)
                            ->rules([
                                function ($get, $record) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                        $supplierId = $get('supplier_id');
                                        if (!$supplierId || !$value) {
                                            return;
                                        }
                                        
                                        $query = \App\Models\AccountPayable::where('supplier_id', $supplierId)
                                            ->where('document_number', $value);
                                        
                                        // Ignorar el registro actual en edición
                                        if ($record) {
                                            $query->where('id', '!=', $record->id);
                                        }
                                        
                                        if ($query->exists()) {
                                            $fail('Ya existe una cuenta por pagar con este número de documento para este proveedor.');
                                        }
                                    };
                                }
                            ])
                            ->columnSpan(1),
                        
                        Select::make('type')
                            ->label('Tipo de Documento')
                            ->options([
                                'invoice' => 'Factura',
                                'receipt' => 'Recibo',
                                'debit_note' => 'Nota de débito',
                                'other' => 'Otro',
                            ])
                            ->default('invoice')
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Información de Fechas y Términos')
                    ->schema([
                        DatePicker::make('issue_date')
                            ->label('Fecha de Emisión')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now())
                            ->maxDate(now())
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $paymentTerms = $get('payment_terms');
                                $paymentPeriod = $get('payment_period');
                                
                                if ($state && $paymentTerms === 'credit' && $paymentPeriod) {
                                    $dueDate = \Carbon\Carbon::parse($state)->addDays($paymentPeriod);
                                    $set('due_date', $dueDate->format('Y-m-d'));
                                }
                            }),
                        
                        Select::make('payment_terms')
                            ->label('Términos de Pago')
                            ->options([
                                'cash' => 'Efectivo',
                                'credit' => 'Crédito',
                            ])
                            ->default('credit')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state === 'cash') {
                                    $issueDate = $get('issue_date');
                                    if ($issueDate) {
                                        $set('due_date', $issueDate);
                                    }
                                    $set('payment_period', null);
                                }
                            }),
                        
                        TextInput::make('payment_period')
                            ->label('Período de Pago (días)')
                            ->readOnly()
                            ->dehydrated(false)
                            ->visible(fn ($get) => $get('payment_terms') === 'credit')
                            ->helperText('Se calcula automáticamente desde la fecha de vencimiento'),
                        
                        DatePicker::make('due_date')
                            ->label('Fecha de Vencimiento')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->minDate(fn (callable $get) => $get('issue_date'))
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (! $state) {
                                    return;
                                }

                                $paymentTerms = $get('payment_terms');
                                $issueDate = $get('issue_date');

                                if ($paymentTerms === 'credit' && $issueDate) {
                                    $days = \Carbon\Carbon::parse($issueDate)->diffInDays(\Carbon\Carbon::parse($state));
                                    $set('payment_period', $days > 0 ? $days : 0);
                                }
                            })
                            ->helperText('Debe ser igual o posterior a la fecha de emisión'),
                    ])
                    ->columns(2),

                Section::make('Información Financiera')
                    ->schema([
                        TextInput::make('total_amount')
                            ->label('Monto Total')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->prefix('$')
                            ->reactive(),
                        
                        TextInput::make('paid_amount')
                            ->label('Monto Pagado')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->rules([
                                function ($get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $totalAmount = (float) ($get('total_amount') ?? 0);
                                        $value = (float) $value;
                                        
                                        if ($value > $totalAmount) {
                                            $fail('El monto pagado no puede ser mayor al monto total.');
                                        }
                                    };
                                }
                            ])
                            ->helperText('Debe ser menor o igual al monto total'),
                        
                        DatePicker::make('payment_date')
                            ->label('Fecha de Pago')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->minDate(fn (callable $get) => $get('issue_date'))
                            ->maxDate(now())
                            ->helperText('Opcional: debe ser igual o posterior a la fecha de emisión'),
                        
                        Select::make('status')
                            ->label('Estado')
                            ->options(function (callable $get, $record): array {
                                $total = (float) ($get('total_amount') ?? 0);
                                $paid = (float) ($get('paid_amount') ?? 0);
                                $currentStatus = $record?->status;

                                // Si la cuenta está en estado Pagado, solo permitir cambio a Anulado
                                if ($currentStatus === 'paid') {
                                    return [
                                        'paid' => 'Pagado',
                                        'voided' => 'Anulado',
                                    ];
                                }

                                // Para otros estados (Pendiente, Parcial), mostrar opciones normales
                                $options = [
                                    'pending' => 'Pendiente',
                                    'partial' => 'Parcial',
                                    'voided' => 'Anulado',
                                ];

                                if ($total > 0 && $paid === $total) {
                                    $options['paid'] = 'Pagado';
                                }

                                return $options;
                            })
                            ->default('pending')
                            ->required()
                            ->reactive()
                            ->helperText('Si está Pagado, solo se puede cambiar a Anulado')
                            ->rules([
                                function ($get, $record) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                        if ($record?->status === 'paid' && !in_array($value, ['paid', 'voided'])) {
                                            $fail('Cuando una cuenta está Pagada, solo puede cambiar a estado Anulado.');
                                        }
                                    };
                                }
                            ]),
                    ])
                    ->columns(2),
            ]);
    }
}
