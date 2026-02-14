<?php

namespace App\Filament\Resources\AccountReceivables\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AccountReceivableInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('customer.name')
                    ->label('Cliente'),
                TextEntry::make('invoice_number')
                    ->label('Número de Factura'),
                TextEntry::make('issue_date')
                    ->label('Fecha de Emisión')
                    ->date(),
                TextEntry::make('due_date')
                    ->label('Fecha de Vencimiento')
                    ->date(),
                TextEntry::make('description')
                    ->label('Descripción'),
                TextEntry::make('total_amount')
                    ->label('Monto Total')
                    ->money('CRC'),
                    //->numeric(),
                TextEntry::make('paid_amount')
                    ->label('Monto Pagado')
                    ->money('CRC'),
                    //->numeric(),
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
