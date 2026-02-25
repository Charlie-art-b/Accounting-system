<?php

namespace App\Filament\Resources\Users\Pages;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Throwable;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

  protected function beforeCreate(): void
{
    $currentUser = auth()->user();

    // 🔹 1. Validar permiso para crear usuarios
    if (!$currentUser->can('users.create')) {

        Notification::make()
            ->danger()
            ->title('Acción no autorizada')
            ->body('No tienes permiso para crear usuarios.')
            ->persistent()
            ->send();

        $this->halt();
    }

    $rolesIds = $this->data['roles'] ?? [];

    if (!is_array($rolesIds)) {
        $rolesIds = [$rolesIds];
    }

    $roles = Role::whereIn('id', $rolesIds)->get();

    foreach ($roles as $role) {
        foreach ($role->permissions as $permission) {

            if (!$currentUser->can($permission->name)) {

                Notification::make()
                    ->danger()
                    ->title('Rol no permitido')
                    ->body("No puedes asignar el rol '{$role->name}' porque contiene permisos superiores a los tuyos.")
                    ->persistent()
                    ->send();

                $this->halt();
            }
        }
    }
}
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['password'])) {
            throw ValidationException::withMessages([
                'password' => 'La contraseña es obligatoria.',
            ]);
        }

        $data['password'] = Hash::make($data['password']);

        return $data;
    }


    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (Throwable $e) {

            Notification::make()
                ->danger()
                ->title('Error al crear usuario')
                ->body('Ocurrió un error inesperado. Verifica los datos.')
                ->persistent()
                ->send();

            throw $e;
        }
    }
    protected function afterCreate(): void
{
    $rolesIds = $this->data['roles'] ?? [];

    // Si no seleccionaron rol → asignar el más básico
    if (empty($rolesIds)) {

        $basicRole = Role::where('name', 'asistente')->first();

        if ($basicRole) {
            $this->record->assignRole($basicRole);
        }

        return;
    }

    // Si seleccionaron rol → asignarlo normalmente
    if (!is_array($rolesIds)) {
        $rolesIds = [$rolesIds];
    }

    $roles = Role::whereIn('id', $rolesIds)->pluck('name')->toArray();

    $this->record->syncRoles($roles);
}
}