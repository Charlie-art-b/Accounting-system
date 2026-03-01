<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Detalles del usuario';

    protected function getHeaderActions(): array
    {
        return [

            Action::make('volver')
                ->label('')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->url(UserResource::getUrl('index')),

            EditAction::make()
                ->label('Editar')
                ->visible(fn () => auth()->user()?->can('users.update')),
        ];
    }
}