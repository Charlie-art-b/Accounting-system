<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Resources\Suppliers\SupplierResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;

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
                ->before(function (DeleteAction $action) {
                    // Verificar cuentas por pagar con saldo pendiente
                    $pendingAccounts = $this->record->cuentasPorPagar()
                        ->whereIn('status', ['pending', 'partial'])
                        ->count();

                    if ($pendingAccounts > 0) {
                        Notification::make()
                            ->title('NO SE PUEDE ELIMINAR')
                            ->body("Este proveedor tiene {$pendingAccounts} cuenta(s) por pagar con saldo pendiente. Solo se pueden eliminar proveedores sin deudas pendientes.")
                            ->danger()
                            ->send();

                        throw new Halt();
                    }

                    // Verificar productos asociados
                    $productsCount = $this->record->productos()->count();
                    if ($productsCount > 0) {
                        Notification::make()
                            ->title('NO SE PUEDE ELIMINAR')
                            ->body("Este proveedor tiene {$productsCount} producto(s) asociado(s). Elimina o cambia el proveedor de los productos antes de continuar.")
                            ->danger()
                            ->send();

                        throw new Halt();
                    }
                })
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('¡Proveedor eliminado!')
                        ->body('El proveedor ha sido eliminado correctamente.')
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
            ->body('Los cambios del proveedor se han guardado correctamente.');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar cambios')
                ->requiresConfirmation()
                ->modalHeading('Confirmar cambios')
                ->modalDescription('¿Deseas guardar los cambios de este proveedor?')
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
        return $this->getResource()::getUrl('index');
    }
}
