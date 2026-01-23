<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('name')),
                TextEntry::make('first_last_name')
                    ->label(__('first_last_name')),
                TextEntry::make('second_last_name')
                    ->label(__('second_last_name'))
                    ->placeholder('-'),
                TextEntry::make('id_type')
                    ->label(__('id_type'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'identification' => 'Cédula',
                        'dimex' => 'DIMEX',
                        'passport' => 'Pasaporte',
                        default => $state,
                    }),
                TextEntry::make('identification')
                    ->label(__('identification')),
                TextEntry::make('email')
                    ->label(__('email')),
                TextEntry::make('phone')
                    ->label(__('phone'))
                    ->placeholder('-'),
                TextEntry::make('address')
                    ->label(__('address'))
                    ->placeholder('-'),
                TextEntry::make('customer_type')
                    ->label(__('customer_type'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'individual' => 'Persona física',
                        'legal_person' => 'Persona jurídica',
                        default => $state,
                    }),
                IconEntry::make('status')
                    ->label(__('status'))
                    ->boolean(),
                TextEntry::make('notes')
                    ->label(__('notes'))
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->label(__('created_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(__('updated_at'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
