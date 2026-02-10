<?php

namespace App\Filament\Resources\FixedAssets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FixedAssetInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('asset_name'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('acquisition_value')
                    ->numeric(),
                TextEntry::make('acquisition_date')
                    ->date(),
                TextEntry::make('useful_life_years')
                    ->numeric(),
                TextEntry::make('residual_value')
                    ->numeric(),
                TextEntry::make('accumulated_depreciation')
                    ->numeric(),
                TextEntry::make('net_value')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('disposal_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('disposal_reason')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
