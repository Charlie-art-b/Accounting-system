<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

 
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('¡Cliente creado!')
            ->body('El cliente se ha creado correctamente.');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Crear')
                ->keyBindings(['mod+s'])
                ->requiresConfirmation()
                ->modalHeading('Confirmar creación')
                ->modalDescription('¿Deseas registrar este cliente? Revisa los datos antes de confirmar.')
                ->modalSubmitActionLabel('Sí, crear')
                ->modalCancelActionLabel('No, cancelar')
                ->action(fn () => $this->create())
                ->visible(fn () => Auth::user()?->can('customers.create')),

            Action::make('cancel')
                ->label('Cancelar')
                ->color('gray')
                ->url($this->getResource()::getUrl('index'))
                ->visible(fn () => Auth::user()?->can('customers.view')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->url($this->getResource()::getUrl('index'))
                ->tooltip('Volver a la lista')
                ->visible(fn () => Auth::user()?->can('customers.view')),
        ];
    }
}