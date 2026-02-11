<?php

namespace App\Filament\Resources\CollectionManagement\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CollectionManagementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('account_receivable_id')
                    ->relationship('accountReceivable', 'id')
                    ->required(),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                DateTimePicker::make('next_reminder_at'),
                TextInput::make('reminder_attempts')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('last_action')
                    ->default(null),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
