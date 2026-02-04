<?php

namespace App\Filament\Resources\AccountPayables\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AccountPayableInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('supplier.id')
                    ->label('Supplier'),
                TextEntry::make('document_number'),
                TextEntry::make('issue_date')
                    ->date(),
                TextEntry::make('payment_terms')
                    ->badge(),
                TextEntry::make('payment_period')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('due_date')
                    ->date(),
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('total_amount')
                    ->numeric(),
                TextEntry::make('paid_amount')
                    ->numeric(),
                TextEntry::make('payment_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
