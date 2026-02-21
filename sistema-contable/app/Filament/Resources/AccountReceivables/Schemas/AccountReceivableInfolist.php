<?php

namespace App\Filament\Resources\AccountReceivables\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccountReceivableInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informacion del Cliente')
                    ->schema([
                        TextEntry::make('customer.name')
                            ->label('Cliente'),
                        TextEntry::make('invoice_number')
                            ->label('Numero de Factura')
                            ->copyable()
                            ->weight('bold'),
                        TextEntry::make('description')
                            ->label('Descripcion')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Fechas y Estado')
                    ->schema([
                        TextEntry::make('issue_date')
                            ->label('Fecha de Emision')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-calendar'),
                        TextEntry::make('due_date')
                            ->label('Fecha de Vencimiento')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-clock')
                            ->color(fn ($record): string =>
                                $record->due_date < now() && $record->status !== 'paid'
                                    ? 'danger'
                                    : 'gray'
                            ),
                        TextEntry::make('status')
                            ->label('Estado')
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'pending' => 'Pendiente',
                                'partial' => 'Parcial',
                                'paid' => 'Pagado',
                                default => $state,
                            })
                            ->colors([
                                'danger' => 'pending',
                                'warning' => 'partial',
                                'success' => 'paid',
                            ])
                            ->badge(),
                    ])
                    ->columns(3),

                Section::make('Informacion Financiera')
                    ->schema([
                        TextEntry::make('total_amount')
                            ->label('Monto Total')
                            ->money('CRC')
                            ->weight('bold')
                            ->size('lg'),
                        TextEntry::make('paid_amount')
                            ->label('Monto Pagado')
                            ->money('CRC')
                            ->color('success'),
                        TextEntry::make('pending_amount')
                            ->label('Saldo Pendiente')
                            ->money('CRC')
                            ->color(fn ($record): string => $record->pending_amount > 0 ? 'warning' : 'success')
                            ->weight('bold'),
                    ])
                    ->columns(2),

                Section::make('Informacion del Registro')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Creado en')
                            ->dateTime('d/m/Y H:i:s')
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Actualizado en')
                            ->dateTime('d/m/Y H:i:s')
                            ->placeholder('-'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
