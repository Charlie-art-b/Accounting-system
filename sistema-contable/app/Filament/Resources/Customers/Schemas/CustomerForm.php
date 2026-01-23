<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->minLength(2)
                    ->maxLength(100)
                    ->regex('/^[\p{L}\p{N}\s]+$/u'),

                TextInput::make('first_last_name')
                    ->label(__('First last name'))
                    ->required()
                    ->minLength(2)
                    ->maxLength(50)
                    ->regex('/^[\p{L}\p{N}\s]+$/u'),

                TextInput::make('second_last_name')
                    ->label(__('Second last name'))
                    ->minLength(2)
                    ->maxLength(50)
                    ->regex('/^[\p{L}\p{N}\s]+$/u')
                    ->default(null),

                Select::make('id_type')
                    ->label(__('Identification type'))
                    ->options([
                        'identification' => __('ID'),
                        'dimex' => __('DIMEX'),
                        'passport' => __('Passport'),
                    ])
                    ->default('identification')
                    ->required(),

                TextInput::make('identification')
                    ->label(__('Identification'))
                    ->required()
                    ->maxLength(20)
                    ->unique(table: 'customers', column: 'identification', ignoreRecord: true)
                    ->regex('/^(\d{1}[-\s]?\d{4}[-\s]?\d{4}|\d{11,12}|[A-Z0-9]{6,12})$/i'),

                TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(table: 'customers', column: 'email', ignoreRecord: true),

                TextInput::make('phone')
                    ->label(__('Phone number'))
                    ->tel()
                    ->nullable()
                    ->minLength(8)
                    ->maxLength(20)
                    ->default(null)
                    ->regex('/^[0-9()+\-\s]+$/'),

                TextInput::make('address')
                    ->label(__('Address'))
                    ->nullable()
                    ->maxLength(355),

                Select::make('customer_type')
                    ->label(__('Customer type'))
                    ->options([
                        'individual' => __('Individual'),
                        'legal_person' => __('Legal person'),
                    ])
                    ->default('individual')
                    ->required(),

                Toggle::make('status')
                    ->label(__('Status'))
                    ->helperText(__('If deactivated, the customer becomes inactive in the system.'))
                    ->default(true)
                    ->required(),

                Textarea::make('notes')
                    ->label(__('Notes'))
                    ->placeholder(__('Write additional customer information (optional).'))
                    ->nullable()
                    ->maxLength(2000)
                    ->columnSpanFull(),
            ]);
    }
}
