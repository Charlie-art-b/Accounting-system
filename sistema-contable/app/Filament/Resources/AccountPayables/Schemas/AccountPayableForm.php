<?php

namespace App\Filament\Resources\AccountPayables\Schemas;

use App\Models\AccountPayable;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class AccountPayableForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $canCreate = $user?->can('account_payables.create') ?? false;
        $canUpdate = $user?->can('account_payables.update') ?? false;

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
                            ->disabled(fn ($record) =>
                                (!$canCreate && !$record) ||
                                (!$canUpdate && $record)
                            )
                            ->columnSpan(2),

                        TextInput::make('document_number')
                            ->label('Número de Documento')
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn ($record) =>
                                (!$canCreate && !$record) ||
                                (!$canUpdate && $record)
                            )
                            ->rules([
                                function ($get, $record) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get, $record) {

                                        $supplierId = $get('supplier_id');

                                        if (!$supplierId || !$value) return;

                                        $query = AccountPayable::where('supplier_id', $supplierId)
                                            ->where('document_number', $value);

                                        if ($record) {
                                            $query->where('id', '!=', $record->id);
                                        }

                                        if ($query->exists()) {
                                            $fail('Ya existe una cuenta con este número para este proveedor.');
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
                            ->disabled(fn ($record) =>
                                (!$canCreate && !$record) ||
                                (!$canUpdate && $record)
                            )
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
                            ->disabled(fn ($record) =>
                                (!$canCreate && !$record) ||
                                (!$canUpdate && $record?->status === 'paid')
                            ),

                        DatePicker::make('due_date')
                            ->label('Fecha de Vencimiento')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->minDate(fn (callable $get) => $get('issue_date'))
                            ->disabled(fn ($record) =>
                                (!$canCreate && !$record) ||
                                (!$canUpdate && $record?->status === 'paid')
                            ),
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
                            ->disabled(fn ($record) =>
                                (!$canCreate && !$record) ||
                                (!$canUpdate && $record?->status === 'paid')
                            ),

                        TextInput::make('paid_amount')
                            ->label('Monto Pagado')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('$')
                            ->default(0)
                            ->disabled(fn () => true), 

                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'partial' => 'Parcial',
                                'paid' => 'Pagado',
                                'voided' => 'Anulado',
                            ])
                            ->default('pending')
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