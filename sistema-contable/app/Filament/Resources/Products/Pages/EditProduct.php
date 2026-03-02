<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Volver a la lista')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
            
            ViewAction::make(),
            DeleteAction::make()
                ->before(function (DeleteAction $action) {
                    $inventoryCount = $this->record->inventoryProduct()->count();

                    if ($inventoryCount > 0) {
                        Notification::make()
                            ->title('NO SE PUEDE ELIMINAR')
                            ->body('Este producto está registrado en el inventario. Elimínalo del inventario primero.')
                            ->danger()
                            ->send();

                        throw new Halt();
                    }
                })
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('¡Producto eliminado!')
                        ->body('El producto ha sido eliminado correctamente.')
                )
                ->after(function () {
                    return redirect()->to($this->getResource()::getUrl('index'));
                }),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('¡Cambios guardados!')
            ->body('Los cambios del producto se han guardado correctamente.');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar cambios')
                ->requiresConfirmation()
                ->modalHeading('Confirmar cambios')
                ->modalDescription('¿Deseas guardar los cambios de este producto?')
                ->modalSubmitActionLabel('Sí, guardar')
                ->modalCancelActionLabel('Cancelar')
                ->action(fn () => $this->save()),

            Action::make('cancel')
                ->label('Cancelar')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
