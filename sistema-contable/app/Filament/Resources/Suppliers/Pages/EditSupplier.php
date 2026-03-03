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

    /**
     * Control de acceso a la página
     */
    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()->can('suppliers.update');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Volver')
                ->label('Volver a la lista')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),

            ViewAction::make()
                ->visible(fn () => auth()->user()->can('suppliers.view')),

            DeleteAction::make()
                ->visible(fn () => auth()->user()->can('suppliers.delete'))
                ->before(function (DeleteAction $action) {
                    $pendingAccounts = $this->record->cuentasPorPagar()
                        ->whereIn('status', ['pending', 'partial'])
                        ->count();

                    if ($pendingAccounts > 0) {
                        Notification::make()
                            ->title('NO SE PUEDE ELIMINAR')
                            ->body("Este proveedor tiene {$pendingAccounts} cuenta(s) por pagar con saldo pendiente.")
                            ->danger()
                            ->send();

                        throw new Halt();
                    }
                    $productsCount = $this->record->productos()->count();
                    if ($productsCount > 0) {
                        Notification::make()
                            ->title('NO SE PUEDE ELIMINAR')
                            ->body("Este proveedor tiene {$productsCount} producto(s) asociado(s).")
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
                ->after(fn () => redirect()->to($this->getResource()::getUrl('index'))),
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
                ->visible(fn () => auth()->user()->can('suppliers.update'))
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