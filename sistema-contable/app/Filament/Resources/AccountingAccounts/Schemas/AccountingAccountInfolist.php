<?php

namespace App\Filament\Resources\AccountingAccounts\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AccountingAccountInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Información General')
                    ->description('Datos básicos de la cuenta contable')
                    ->schema([
                        TextEntry::make('customer.name')
                            ->label('Cliente'),

                        TextEntry::make('code')
                            ->label('Código'),

                        TextEntry::make('name')
                            ->label('Nombre'),
                    ])
                    ->columns(2),

                Section::make('Clasificación')
                    ->description('Tipo y clasificación de la cuenta')
                    ->schema([
                        TextEntry::make('type')
                            ->label('Tipo')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('classification')
                            ->label('Clasificación')
                            ->placeholder('-'),

                        TextEntry::make('report_section')
                            ->label('Sección del Reporte')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Estructura Jerárquica')
                    ->description('Organización de cuentas padre e hijos')
                    ->schema([
                        TextEntry::make('parent.display')
                            ->label('Cuenta Padre')
                            ->placeholder('Sin cuenta padre'),

                        TextEntry::make('level')
                            ->label('Nivel'),

                        TextEntry::make('children_count')
                            ->label('Cuentas hijas')
                            ->state(fn ($record) => $record->children()->count() ?? 0),
                    ])
                    ->columns(3),

                Section::make('Configuración')
                    ->description('Naturaleza y estado de la cuenta')
                    ->schema([
                        TextEntry::make('normal_balance')
                            ->label('Naturaleza')
                            ->formatStateUsing(fn ($state) => $state === 'debit' ? 'Deudora' : 'Acreedora')
                            ->badge()
                            ->color(fn ($state) => $state === 'debit' ? 'warning' : 'success'),

                        TextEntry::make('status')
                            ->label('Estado')
                            ->badge()
                            ->color(fn ($state) => $state === 'Activa' ? 'success' : 'danger'),

                        TextEntry::make('saldo')
                            ->label('Saldo')
                            ->state(fn ($record) => $record->getSaldo())
                            ->money('CRC'),
                    ])
                    ->columns(3),

                Section::make('Auditoría')
                    ->description('Registro de cambios')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Creado en')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Última actualización')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(2),
            ]);
    }
}
