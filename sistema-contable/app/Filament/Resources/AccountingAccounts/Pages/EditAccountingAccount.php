<?php

namespace App\Filament\Resources\AccountingAccounts\Pages;

use App\Filament\Resources\AccountingAccounts\AccountingAccountResource;
use App\Models\AccountingAccount;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Actions\ViewAction;

class EditAccountingAccount extends EditRecord
{
    protected static string $resource = AccountingAccountResource::class;

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Cambios guardados')
            ->body('Los cambios de la cuenta contable se han guardado correctamente.');
    }

    protected function getRedirectUrl(): string
    {
        return AccountingAccountResource::getUrl('view', ['record' => $this->record]);
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
                ->requiresConfirmation()
                ->before(function (AccountingAccount $record, DeleteAction $action) {
                    if ($record->journalLines()->exists()) {
                        Notification::make()
                            ->danger()
                            ->title('No se puede eliminar')
                            ->body('Esta cuenta tiene movimientos contables asociados.')
                            ->send();

                        $action->halt();
                    }

                    if ($record->children()->exists()) {
                        Notification::make()
                            ->danger()
                            ->title('No se puede eliminar')
                            ->body('Esta cuenta tiene subcuentas asignadas.')
                            ->send();

                        $action->halt();
                    }
                })
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Cuenta eliminada')
                        ->body('La cuenta contable ha sido eliminada correctamente.')
                ),
        ];
    }

    protected function getFormActions(): array
    {        
        return [
            Action::make('save')
                ->label('Guardar cambios')
                ->requiresConfirmation()
                ->modalHeading('Confirmar cambios')
                ->modalDescription('¿Deseas guardar los cambios de esta cuenta contable? Revisa los datos antes de confirmar.')
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
