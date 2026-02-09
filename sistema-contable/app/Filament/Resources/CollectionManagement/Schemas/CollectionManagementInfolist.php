<?php

namespace App\Filament\Resources\CollectionManagement\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CollectionManagementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('accountReceivable.id')
                    ->label('Account receivable'),
                TextEntry::make('customer.name')
                    ->label('Customer'),
                TextEntry::make('next_reminder_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('reminder_attempts')
                    ->numeric(),
                TextEntry::make('last_action')
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
