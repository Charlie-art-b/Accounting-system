<?php

namespace App\Filament\Resources\FixedAssets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FixedAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Información General')
                    ->description('Identificación del activo fijo')
                    ->icon('heroicon-o-briefcase')
                    ->columns(2)
                    ->schema([
                        TextInput::make('asset_name')
                            ->label('Nombre del Activo')
                            ->placeholder('Ej: Equipo de oficina')
                            ->required()
                            ->columnSpanFull()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('Descripción')
                            ->placeholder('Describe el activo')
                            ->default(null)
                            ->columnSpanFull()
                            ->rows(3),

                        DatePicker::make('acquisition_date')
                            ->label('Fecha de Adquisición')
                            ->required(),

                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'active' => 'Activo',
                                'disposed' => 'Dado de Baja',
                            ])
                            ->default('active')
                            ->required(),
                    ]),

                Section::make('Valores y Vida Útil')
                    ->description('Montos y depreciación')
                    ->icon('heroicon-o-currency-dollar')
                    ->columns(3)
                    ->schema([
                        TextInput::make('acquisition_value')
                            ->label('Valor de Adquisición')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0),

                        TextInput::make('residual_value')
                            ->label('Valor Residual')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->default(0.0),

                        TextInput::make('accumulated_depreciation')
                            ->label('Depreciación Acumulada')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->default(0.0),

                        TextInput::make('net_value')
                            ->label('Valor Neto')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->default(null)
                            ->helperText('Opcional si se calcula automáticamente'),

                        TextInput::make('useful_life_years')
                            ->label('Vida Útil (años)')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->step(1)
                            ->minValue(1),
                    ]),

                Section::make('Baja del Activo')
                    ->description('Completar solo si el activo fue dado de baja')
                    ->icon('heroicon-o-archive-box')
                    ->columns(2)
                    ->visible(fn (callable $get) => $get('status') === 'disposed')
                    ->schema([
                        DatePicker::make('disposal_date')
                            ->label('Fecha de Baja')
                            ->required(fn (callable $get) => $get('status') === 'disposed'),

                        TextInput::make('disposal_reason')
                            ->label('Motivo de Baja')
                            ->placeholder('Ej: Obsolescencia')
                            ->default(null)
                            ->maxLength(255)
                            ->required(fn (callable $get) => $get('status') === 'disposed'),
                    ]),
            ]);
    }
}
