<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('¡Cambios guardados!')
            ->body('Los cambios del usuario se han guardado correctamente.');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->url($this->getResource()::getUrl('index'))
                ->tooltip('Volver a la lista'),
            ViewAction::make(),
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

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar cambios')
                ->requiresConfirmation()
                ->modalHeading('Confirmar cambios')
                ->modalDescription('¿Deseas guardar los cambios de este usuario?')
                ->modalSubmitActionLabel('Sí, guardar')
                ->modalCancelActionLabel('Cancelar')
                ->action(fn () => $this->save())
                ->visible(fn () => Auth::user()?->can('users.update')),

             Action::make('cancel')
                ->label('Cancelar')
                ->color('gray')
                ->url($this->getResource()::getUrl('index'))
                ->visible(fn () => Auth::user()?->can('users.view')),
        ];
    }

    protected function beforeSave(): void
    {
        $currentUser = auth()->user();

        if (!$currentUser->can('users.update')) {
            Notification::make()
                ->danger()
                ->title('Acción no autorizada')
                ->body('No tienes permiso para editar usuarios.')
                ->persistent()
                ->send();

            $this->halt();
        }

        if ($this->record->id === $currentUser->id) {
            Notification::make()
                ->danger()
                ->title('No permitido')
                ->body('No puedes editar tu propio usuario.')
                ->persistent()
                ->send();

            $this->halt();
        }

        $levels = [
            'administrador' => 4,
            'gerente' => 3,
            'sub-gerente' => 2,
            'asistente' => 1,
        ];

        $currentRole = $currentUser->roles->first()?->name;
        $targetRole = $this->record->roles->first()?->name;

        $currentLevel = $levels[$currentRole] ?? 0;
        $targetLevel = $levels[$targetRole] ?? 0;

        if ($currentLevel <= $targetLevel) {
            Notification::make()
                ->danger()
                ->title('No autorizado')
                ->body('Solo puedes editar usuarios con un rol inferior al tuyo.')
                ->persistent()
                ->send();

            $this->halt();
        }

        $rolesIds = $this->data['roles'] ?? [];

        if (!is_array($rolesIds)) {
            $rolesIds = [$rolesIds];
        }

        $newRoles = Role::whereIn('id', $rolesIds)->get();

        foreach ($newRoles as $role) {
            $newRoleLevel = $levels[$role->name] ?? 0;

            if ($newRoleLevel >= $currentLevel) {
                Notification::make()
                    ->danger()
                    ->title('Rol no permitido')
                    ->body('No puedes asignar un rol igual o superior al tuyo.')
                    ->persistent()
                    ->send();

                $this->halt();
            }
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return UserResource::getUrl('index');
    }
}