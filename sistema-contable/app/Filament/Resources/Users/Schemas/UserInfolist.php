<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextEntry::make('name')
                    ->label('Nombre completo'),

                TextEntry::make('email')
                    ->label('Correo electrónico'),

                TextEntry::make('roles.name')
                    ->label('Rol(es)')
                    ->badge()
                    ->separator(', ')
                    ->placeholder('Sin rol'),

                TextEntry::make('permissions')
                    ->label('Permisos')
                    ->state(function ($record) {
                        return $record->getAllPermissions()
                            ->pluck('name')
                            ->toArray();
                    })
                    ->badge()
                    ->separator(', ')
                    ->placeholder('Sin permisos'),

                TextEntry::make('email_verified_at')
                    ->label('Correo verificado en')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('created_at')
                    ->label('Creado en')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('updated_at')
                    ->label('Última actualización')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}