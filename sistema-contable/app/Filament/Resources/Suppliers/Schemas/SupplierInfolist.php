<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SupplierInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Proveedor')
                    ->description('Datos básicos del proveedor')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('tipo_proveedor')
                            ->label('Tipo de Proveedor')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'persona' => 'Persona Natural',
                                'empresa' => 'Empresa',
                                default => $state,
                            }),
                        
                        TextEntry::make('nombre_razon_social')
                            ->label('Nombre / Razón Social')
                            ->size('lg')
                            ->weight('bold')
                            ->columnSpanFull(),
                    ]),

                Section::make('Identificación')
                    ->description('Documento de identidad')
                    ->schema([
                        TextEntry::make('identificacion')
                            ->label('Identificación')
                            ->copyable()
                            ->weight('bold'),
                    ]),

                Section::make('Contacto')
                    ->description('Información de contacto')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('correo')
                            ->label('Correo Electrónico')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),
                        
                        TextEntry::make('telefono')
                            ->label('Teléfono')
                            ->icon('heroicon-o-phone')
                            ->placeholder('—'),
                    ]),

                Section::make('Estado')
                    ->description('Estado en el sistema')
                    ->schema([
                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'activo' => 'Activo',
                                'inactivo' => 'Inactivo',
                                default => $state,
                            })
                            ->colors([
                                'success' => 'activo',
                                'danger' => 'inactivo',
                            ]),
                    ]),

                Section::make('Clientes Asociados')
                    ->description('Clientes vinculados con este proveedor')
                    ->schema([
                        TextEntry::make('customers')
                            ->label('Clientes')
                            ->getStateUsing(fn ($record) => $record->customers ?? [])
                            ->badge()
                            ->formatStateUsing(fn ($record) => "{$record->name} {$record->first_last_name}")
                            ->separator(',')
                            ->columnSpanFull(),
                    ]),

                Section::make('Información del Registro')
                    ->description('Metadata del registro')
                    ->collapsible()
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Creado en')
                            ->dateTime()
                            ->since(),
                        
                        TextEntry::make('updated_at')
                            ->label('Actualizado en')
                            ->dateTime()
                            ->since(),
                    ]),
            ]);
    }
}