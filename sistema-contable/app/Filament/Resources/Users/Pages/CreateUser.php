<?php

namespace App\Filament\Resources\Users\Pages;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Throwable;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('¡Usuario creado!')
            ->body('El usuario se ha creado correctamente.');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Crear')
                ->keyBindings(['mod+s'])
                ->requiresConfirmation()
                ->modalHeading('Confirmar creación')
                ->modalDescription('¿Deseas registrar este usuario? Revisa los datos antes de confirmar.')
                ->modalSubmitActionLabel('Sí, crear')
                ->modalCancelActionLabel('No, cancelar')
                ->visible(fn () => auth()->user()->can('users.create'))
                ->action(fn () => $this->create()),

            Action::make('cancel')
                ->label('Cancelar')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
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
        ];
    }
    
    protected function beforeCreate(): void
    {
        $currentUser = auth()->user();

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

    protected function handleRecordCreation(array $data):Model
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
        if (empty($rolesIds)) {

            $basicRole = Role::where('name', 'asistente')->first();

            if ($basicRole) {
                $this->record->assignRole($basicRole);
            }

            return;
        }
        if (!is_array($rolesIds)) {
            $rolesIds = [$rolesIds];
        }

        $roles = Role::whereIn('id', $rolesIds)->pluck('name')->toArray();
        $this->record->syncRoles($roles);
    }

}