<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Auth;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return Auth::user()?->can('customers.update') ?? false;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('¡Cambios guardados!')
            ->body('Los cambios del cliente se han guardado correctamente.');
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function getHeaderActions(): array
    {
        return [

            ViewAction::make()
                ->visible(fn () => Auth::user()?->can('customers.view')),

            DeleteAction::make()
                ->visible(fn () => Auth::user()?->can('customers.delete'))
                ->before(function (DeleteAction $action) {
                    $pendingAccounts = $this->record->accountReceivables()
                        ->whereIn('status', ['pending', 'partial'])
                        ->count();

                    if ($pendingAccounts > 0) {
                        Notification::make()
                            ->title('NO SE PUEDE ELIMINAR')
                            ->body("Este cliente tiene {$pendingAccounts} cuenta(s) por cobrar con saldo pendiente. Solo se pueden eliminar clientes sin deudas pendientes.")
                            ->danger()
                            ->send();

                        throw new Halt();
                    }
                })
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('¡Cliente eliminado!')
                        ->body('El cliente ha sido eliminado correctamente.')
                )
                ->after(fn () => redirect()->to(
                    $this->getResource()::getUrl('index')
                )),


            Action::make('back')
                ->label('')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->url($this->getResource()::getUrl('index'))
                ->tooltip('Volver a la lista')
                ->visible(fn () => Auth::user()?->can('customers.view')),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            
            Action::make('save')
                ->label('Guardar cambios')
                ->requiresConfirmation()
                ->modalHeading('Confirmar cambios')
                ->modalDescription('¿Deseas guardar los cambios de este cliente?')
                ->modalSubmitActionLabel('Sí, guardar')
                ->modalCancelActionLabel('Cancelar')
                ->action(fn () => $this->save())
                ->visible(fn () => Auth::user()?->can('customers.update')),

             Action::make('cancel')
                ->label('Cancelar')
                ->color('gray')
                ->url($this->getResource()::getUrl('index'))
                ->visible(fn () => Auth::user()?->can('customers.view')),
        ];
    }
}