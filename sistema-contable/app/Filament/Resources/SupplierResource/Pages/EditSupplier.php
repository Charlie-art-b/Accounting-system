<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;

    /**
     * Mensaje de éxito personalizado después de actualizar
     */
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Proveedor actualizado exitosamente';
    }

    /**
     * Mensaje de éxito en la notificación al actualizar
     */
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('¡Éxito!')
            ->body('El proveedor ' . $this->record->nombre_razon_social . ' ha sido actualizado exitosamente.')
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->successNotificationTitle('Proveedor eliminado'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
