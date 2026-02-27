<?php

namespace App\Filament\Resources\FinancialReports\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FinancialReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                TextInput::make('report_type')
                    ->required(),
                DatePicker::make('fecha_inicio'),
                DatePicker::make('fecha_fin'),
                TextInput::make('tasa_impuestos')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Textarea::make('payload')
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('generated_at')
                    ->required(),
            ]);
    }
}
