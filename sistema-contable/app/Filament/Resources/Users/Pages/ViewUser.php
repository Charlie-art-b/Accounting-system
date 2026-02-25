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
                ->label('Volver')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(UserResource::getUrl('index')),

            EditAction::make()
                ->label('Editar')
                ->visible(fn () => auth()->user()?->can('users.update')),

            DeleteAction::make()
                ->label('Eliminar')
                ->visible(fn () => auth()->user()?->can('users.delete'))
                ->requiresConfirmation()
                ->before(function (User $record, DeleteAction $action) {

                    $currentUser = auth()->user();

                    if ($record->id === $currentUser->id) {
                        Notification::make()
                            ->danger()
                            ->title('No permitido')
                            ->body('No puedes eliminar tu propio usuario.')
                            ->send();

                        $action->halt();
                    }

                    if ($record->hasRole('administrador')) {

                        $adminsCount = User::role('administrador')->count();

                        if ($adminsCount <= 1) {
                            Notification::make()
                                ->danger()
                                ->title('No permitido')
                                ->body('No puedes eliminar el último administrador del sistema.')
                                ->send();

                            $action->halt();
                        }
                    }
                })
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Usuario eliminado')
                        ->body('El usuario fue eliminado correctamente.')
                ),
        ];
    }
}