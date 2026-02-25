<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Role;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
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

    protected function getRedirectUrl(): string
    {
        return UserResource::getUrl('index');
    }
}