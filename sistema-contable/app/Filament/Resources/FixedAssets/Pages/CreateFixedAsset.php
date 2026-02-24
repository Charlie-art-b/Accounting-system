<?php

namespace App\Filament\Resources\FixedAssets\Pages;

use App\Filament\Resources\FixedAssets\FixedAssetResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class CreateFixedAsset extends CreateRecord
{
    protected static string $resource = FixedAssetResource::class;
    
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('¡Activo fijo creado!')
            ->body('El activo fijo se ha creado correctamente.');
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

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Crear')
                ->keyBindings(['mod+s'])
                ->requiresConfirmation()
                ->modalHeading('Confirmar creación')
                ->modalDescription('¿Deseas registrar este activo fijo? Revisa los datos antes de confirmar.')
                ->modalSubmitActionLabel('Sí, crear')
                ->modalCancelActionLabel('No, cancelar')
                ->action(fn () => $this->create()), 
            Action::make('cancel')
                ->label('Cancelar')
                ->color('gray') 
                ->url($this->getResource()::getUrl('index')),  

        ];
    }
}