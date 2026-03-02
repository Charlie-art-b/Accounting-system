<?php

namespace App\Filament\Resources\AccountingAccounts\Pages;

use App\Filament\Resources\AccountingAccounts\AccountingAccountResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class CreateAccountingAccount extends CreateRecord
{
    protected static string $resource = AccountingAccountResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('¡Cuenta contable creada!')
            ->body('La cuenta contable se ha creado correctamente.');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Crear')
                ->keyBindings(['mod+s'])
                ->requiresConfirmation()
                ->modalHeading('Confirmar creación')
                ->modalDescription('¿Deseas registrar esta cuenta contable? Revisa los datos antes de confirmar.')
                ->modalSubmitActionLabel('Sí, crear')
                ->modalCancelActionLabel('No, cancelar')
                ->action(fn () => $this->create()),

            Action::make('cancel')
                ->label('Cancelar')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Volver a la lista')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return AccountingAccountResource::getUrl('view', ['record' => $this->record]);
    }
}
