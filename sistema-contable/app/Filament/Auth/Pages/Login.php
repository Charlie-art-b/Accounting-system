<?php

namespace App\Filament\Auth\Pages;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Placeholder;

class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return parent::form($schema)->components([
            Placeholder::make('credenciales_demo')
                ->label('')
                ->content('Usuario demo: administrador@gmail.com | Contraseña: 12345678'),

            ...parent::form($schema)->getComponents(),
        ]);
    }

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return 'Iniciar Sesión';
    }
}