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
                TextEntry::make('asset_name')
                    ->label('Nombre del Activo'),
    
                TextEntry::make('description')
                    ->label('Descripción')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('acquisition_value')
                    ->label('Valor de Adquisición')
                    ->numeric(),
                TextEntry::make('acquisition_date')
                    ->label('Fecha de Adquisición')
                    ->date(),
                TextEntry::make('useful_life_years')
                    ->label('Vida Útil (años)')
                    ->numeric(),
                TextEntry::make('residual_value')
                    ->label('Valor Residual')
                    ->numeric(),
                TextEntry::make('accumulated_depreciation')
                    ->label('Depreciación Acumulada')
                    ->numeric(),
                TextEntry::make('net_value')
                    ->label('Valor Neto')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->label('Estado')
                    ->badge(),
                TextEntry::make('disposal_date')
                    ->label('Fecha de Baja')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('disposal_reason')
                    ->label('Motivo de Baja')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                        ->label('Creado en')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Actualizado en')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
