<?php

namespace App\Filament\Resources\Inventories\Pages;

use App\Filament\Resources\Inventories\InventoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class EditInventory extends EditRecord
{
    protected static string $resource = InventoryResource::class;

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('¡Cambios guardados!')
            ->body('Los cambios del inventario se han guardado correctamente.');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Volver a la lista')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),

            ViewAction::make(),

            DeleteAction::make()
                ->modalHeading(fn ($record) => "Eliminar inventario '{$record->name}'")
                ->modalDescription('¿Estás seguro de que deseas eliminar este inventario? Solo se puede eliminar si está vacío y sin movimientos. Esta acción no se puede deshacer.')
                ->modalSubmitActionLabel('Sí, eliminar')
                ->successNotificationTitle('Inventario eliminado')
                ->before(function ($action, $record) {
                    $productsWithStock = $record->inventoryProducts()
                        ->get()
                        ->filter(function ($product) {
                            $existence = $product->stock_initial + $product->entries - $product->exits;
                            return $existence > 0;
                        });

                    if ($productsWithStock->count() > 0) {
                        Notification::make()
                            ->danger()
                            ->title('No se puede eliminar el inventario')
                            ->body('No se puede eliminar porque existen productos con stock activo. Debes vaciar el inventario antes de eliminarlo.')
                            ->persistent()
                            ->send();
                        
                        $action->halt();
                    }
                    $productsWithMovements = $record->inventoryProducts()
                        ->where(function ($query) {
                            $query->where('entries', '>', 0)
                                  ->orWhere('exits', '>', 0);
                        })
                        ->count();

                    if ($productsWithMovements > 0) {
                        Notification::make()
                            ->danger()
                            ->title('No se puede eliminar el inventario')
                            ->body('No se puede eliminar porque existen movimientos registrados (entradas o salidas).')
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
                ->label('Guardar cambios')
                ->requiresConfirmation()
                ->modalHeading('Confirmar cambios')
                ->modalDescription('¿Deseas guardar los cambios de este inventario?')
                ->modalSubmitActionLabel('Sí, guardar')
                ->modalCancelActionLabel('Cancelar')
                ->action(fn () => $this->save()),

            Action::make('cancel')
                ->label('Cancelar')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
