<?php

namespace App\Filament\Resources\FixedAssets\Pages;

use App\Filament\Resources\FixedAssets\FixedAssetResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class EditFixedAsset extends EditRecord
{
    protected static string $resource = FixedAssetResource::class;

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('¡Cambios guardados!')
            ->body('Los cambios del activo fijo se han guardado correctamente.');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
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
                ->modalHeading('Eliminar activo fijo')
                ->modalDescription('Solo se pueden eliminar activos activos sin depreciación registrada. Esta acción no se puede deshacer.')
                ->modalSubmitActionLabel('Sí, eliminar')
                ->successNotificationTitle('Activo fijo eliminado')
                ->before(function ($action, $record) {
                    $hasDepreciation = (float) $record->accumulated_depreciation > 0;
                    $isDisposed = $record->status === 'disposed' || $record->disposal_date || $record->disposal_reason;

                    if ($isDisposed || $hasDepreciation) {
                        Notification::make()
                            ->danger()
                            ->title('No se puede eliminar el activo fijo')
                            ->body('Solo se pueden eliminar activos activos sin depreciación registrada.')
                            ->persistent()
                            ->send();

                        $action->halt();
                    }
                }),
        ];
    }
     protected function getFormActions(): array
    {
        return [    
            Action::make('save')
                ->label('Guardar')
                ->keyBindings(['mod+s'])
                ->requiresConfirmation()
                ->modalHeading('Confirmar actualización')
                ->modalDescription('¿Deseas guardar los cambios en este activo fijo? Revisa los datos antes de confirmar.')
                ->modalSubmitActionLabel('Sí, guardar')
                ->modalCancelActionLabel('No, cancelar')
                ->action(fn () => $this->save()), 
            Action::make('cancel')
                ->label('Cancelar')
                ->color('gray') 
                ->url($this->getResource()::getUrl('index')),  
        ];
    }
}