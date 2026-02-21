<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información Personal')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre')
                            ->weight('bold')
                            ->size('lg'),
                        TextEntry::make('first_last_name')
                            ->label('Primer apellido'),
                        TextEntry::make('second_last_name')
                            ->label('Segundo apellido')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Identificación')
                    ->schema([
                        TextEntry::make('id_type')
                            ->label('Tipo de identificación')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'identification' => 'Cédula',
                                'dimex' => 'DIMEX',
                                'passport' => 'Pasaporte',
                                default => $state,
                            }),
                        TextEntry::make('identification')
                            ->label('Identificación')
                            ->copyable()
                            ->weight('bold'),
                    ])
                    ->columns(2),

                Section::make('Contacto')
                    ->schema([
                        TextEntry::make('email')
                            ->label('Correo electrónico')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),
                        TextEntry::make('phone')
                            ->label('Teléfono')
                            ->icon('heroicon-o-phone')
                            ->placeholder('-'),
                        TextEntry::make('address')
                            ->label('Dirección')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Información del Cliente')
                    ->schema([
                        TextEntry::make('customer_type')
                            ->label('Tipo de cliente')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'individual' => 'Persona física',
                                'legal_person' => 'Persona jurídica',
                                default => $state,
                            }),
                        IconEntry::make('status')
                            ->label('Estado')
                            ->boolean(),
                    ])
                    ->columns(2),

                Section::make('Notas y Proveedores')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Notas')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('suppliers')
                            ->label('Proveedores asociados')
                            ->badge()
                            ->getStateUsing(fn ($record) => $record->suppliers->map(fn ($supplier) => "{$supplier->nombre_razon_social} - {$supplier->identificacion}")->toArray())
                            ->placeholder('Sin proveedores asociados')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Información del Registro')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Creado en')
                            ->dateTime('d/m/Y H:i:s'),
                        TextEntry::make('updated_at')
                            ->label('Actualizado en')
                            ->dateTime('d/m/Y H:i:s')
                            ->since(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
