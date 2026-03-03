<?php

namespace App\Filament\Resources\AccountReceivables\Pages;

use App\Filament\Resources\AccountReceivables\AccountReceivableResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class EditAccountReceivable extends EditRecord
{
    protected static string $resource = AccountReceivableResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('¡Cambios guardados!')
            ->body('Los cambios de la cuenta por cobrar se han guardado correctamente.');
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
                ->visible(fn () => auth()->user()?->can('account_receivables.delete'))
                ->before(function (DeleteAction $action) {
                    if (in_array($this->record->status, ['pending', 'partial'], true)) {
                        Notification::make()
                            ->title('NO SE PUEDE ELIMINAR')
                            ->body('Solo se pueden eliminar cuentas por cobrar en estado Pagado.')
                            ->danger()
                            ->send();

                        $action->halt();
                    }
                }),
        ];
    }
    protected function getFormActions(): array
    {        return [
            Action::make('save')
                ->label('Guardar cambios')
                ->requiresConfirmation()
                ->modalHeading('Confirmar cambios')
                ->modalDescription('¿Deseas guardar los cambios de esta cuenta por cobrar? Revisa los datos antes de confirmar.')
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