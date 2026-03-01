<?php

namespace App\Filament\Resources\AccountPayables\Pages;

use App\Filament\Resources\AccountPayables\AccountPayableResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class EditAccountPayable extends EditRecord
{
    protected static string $resource = AccountPayableResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('¡Cambios guardados!')
            ->body('Los cambios de la cuenta por pagar se han guardado correctamente.');
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
                ->before(function (DeleteAction $action) {
                    if (!in_array($this->record->status, ['voided', 'paid'])) {
                        Notification::make()
                            ->danger()
                            ->title('No se puede eliminar')
                            ->body('Esta cuenta debe estar en estado Pagado o Anulado.')
                            ->send();

                        $action->halt();
                    }
                })
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Cuenta eliminada')
                        ->body('La cuenta por pagar ha sido eliminada correctamente.')
                )
                ->after(function () {
                    return redirect()->to($this->getResource()::getUrl('index'));
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
                ->modalDescription('¿Deseas guardar los cambios de esta cuenta por pagar? Revisa los datos antes de confirmar.')
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
