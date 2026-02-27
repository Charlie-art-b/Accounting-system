<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name')
                    ->label('Nombre completo')
                    ->required()
                    ->minLength(3)
                    ->maxLength(255)
                    ->validationMessages([
                        'required' => 'El nombre es obligatorio.',
                        'min' => 'El nombre debe tener al menos 3 caracteres.',
                    ]),

                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'required' => 'El correo es obligatorio.',
                        'email' => 'Debe ingresar un correo válido.',
                        'unique' => 'Este correo ya está registrado.',
                    ]),

                TextInput::make('password')
                    ->label('Contraseña')
                    ->revealable()
                    ->password()
                    ->minLength(6)
                    ->required(fn ($livewire) => $livewire instanceof \App\Filament\Resources\Users\Pages\CreateUser)
                    ->dehydrated(fn ($state) => filled($state))
                    ->validationMessages([
                        'required' => 'La contraseña es obligatoria.',
                        'min' => 'La contraseña debe tener al menos 6 caracteres.',
                    ]),

                TextInput::make('password_confirmation')
                    ->label('Confirmar contraseña')
                    ->password()
                    ->same('password')
                    ->required(fn ($livewire) => $livewire instanceof \App\Filament\Resources\Users\Pages\CreateUser)
                    ->dehydrated(false)
                    ->validationMessages([
                        'same' => 'Las contraseñas no coinciden.',
                    ]),

                Select::make('roles')
                    ->label('Rol')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->multiple()
                    ->searchable()
                    ->required()
                    ->validationMessages([
                        'required' => 'Debe asignar un rol al usuario.',
                    ])
                    ->rules([
                        function ($get, $record) {
                            return function (string $attribute, $value, $fail) use ($record) {
                                if (!$record) return;

                                if ($record->hasRole('administrador') &&
                                    !in_array('administrador', $value)) {

                                    $adminsCount = User::role('administrador')->count();

                                    if ($adminsCount <= 1) {
                                        $fail('No puedes quitar el rol al último administrador del sistema.');
                                    }
                                }
                            };
                        },
                    ]),
            ]);
    }
}