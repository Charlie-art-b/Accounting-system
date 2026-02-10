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
                    ->required(),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('acquisition_value')
                    ->required()
                    ->numeric(),
                DatePicker::make('acquisition_date')
                    ->required(),
                TextInput::make('useful_life_years')
                    ->required()
                    ->numeric(),
                TextInput::make('residual_value')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('accumulated_depreciation')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('net_value')
                    ->numeric()
                    ->default(null),
                Select::make('status')
                    ->options(['active' => 'Active', 'disposed' => 'Disposed'])
                    ->default('active')
                    ->required(),
                DatePicker::make('disposal_date'),
                TextInput::make('disposal_reason')
                    ->default(null),
            ]);
    }
}
