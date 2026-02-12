<?php

namespace App\Filament\Resources\FixedAssets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FixedAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('asset_name')
                    ->label('Nombre del Activo')
                    ->required(),
                Textarea::make('description')
                   ->label('Descripción')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('acquisition_value')
                    ->label('Valor de Adquisición')
                    ->required()
                    ->numeric(),
                DatePicker::make('acquisition_date')
                    ->label('Fecha de Adquisición')
                    ->required(),
                TextInput::make('useful_life_years')
                    ->label('Vida Útil (años)')
                    ->required()
                    ->numeric(),
                TextInput::make('residual_value')
                    ->label('Valor Residual')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('accumulated_depreciation')
                    ->label('Depreciación Acumulada')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('net_value')
                    ->label('Valor Neto')
                    ->numeric()
                    ->default(null),
                Select::make('status')
                    ->label('Estado')
                    ->options(['active' => 'Active', 'disposed' => 'Disposed'])
                    ->default('active')
                    ->required(),
                DatePicker::make('disposal_date')
                    ->label('Fecha de Baja'),
                TextInput::make('disposal_reason')
                    ->label('Motivo de Baja')
                    ->default(null),
            ]);
    }
}
