<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SupplierInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Supplier Name'),
                TextEntry::make('contact_email')
                    ->label('Contact Email'),
                TextEntry::make('phone_number')
                    ->label('Phone Number'),
                TextEntry::make('address')
                    ->label('Address'),
            ]);
    }
}