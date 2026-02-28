<?php

namespace App\Filament\Resources\FixedAssets\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FixedAssetInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información General')
                    ->description('Datos principales del activo')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('asset_name')
                            ->label('Nombre del Activo')
                            ->size('lg')
                            ->weight('bold')
                            ->columnSpanFull(),

                        TextEntry::make('description')
                            ->label('Descripción')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('acquisition_date')
                            ->label('Fecha de Adquisición')
                            ->date(),

                        TextEntry::make('status')
                            ->label('Estado')
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'active' => 'Activo',
                                'disposed' => 'Dado de baja',
                                'under_maintenance' => 'En mantenimiento',
                            
                        default => $state,
                    })
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'disposed' => 'danger',
                                'under_maintenance' => 'warning',
                                default => 'success',
                            }),
                    ]),

                Section::make('Valores y Depreciación')
                    ->description('Montos del activo')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('acquisition_value')
                            ->label('Valor de Adquisición')
                            ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2) : '-')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('residual_value')
                            ->label('Valor Residual')
                            ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2) : '-')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('accumulated_depreciation')
                            ->label('Depreciación Acumulada')
                            ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2) : '-')
                            ->badge()
                            ->color('warning'),

                        TextEntry::make('net_value')
                            ->label('Valor Neto')
                            ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2) : '-')
                            ->badge()
                            ->color('success')
                            ->weight('bold'),

                        TextEntry::make('useful_life_years')
                            ->label('Vida Útil (años)')
                            ->formatStateUsing(fn ($state) => $state !== null ? (string) $state : '-')
                            ->badge()
                            ->color('gray'),
                    ]),

                Section::make('Baja del Activo')
                    ->description('Detalles de baja')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('disposal_date')
                            ->label('Fecha de Baja')
                            ->date()
                            ->placeholder('-'),

                        TextEntry::make('disposal_reason')
                            ->label('Motivo de Baja')
                            ->placeholder('-'),
                    ]),

                Section::make('Información del Registro')
                    ->description('Metadata')
                    ->collapsible()
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Creado en')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Actualizado en')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
