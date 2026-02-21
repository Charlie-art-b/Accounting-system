<?php

namespace App\Filament\Resources\AccountReceivables\Pages;
use Filament\Actions\Action;

use App\Filament\Resources\AccountReceivables\AccountReceivableResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Models\CollectionManagement;

class CreateAccountReceivable extends CreateRecord
{
    protected static string $resource = AccountReceivableResource::class;
    
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('¡Cuenta por cobrar creada!')
            ->body('La cuenta por cobrar se ha creado correctamente.');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Crear')
                ->keyBindings(['mod+s'])
                ->requiresConfirmation()
                ->modalHeading('Confirmar creación')
                ->modalDescription('¿Deseas registrar esta cuenta por cobrar? Revisa los datos antes de confirmar.')
                ->modalSubmitActionLabel('Sí, crear')
                ->modalCancelActionLabel('No, cancelar')
                ->action(fn () => $this->create()),

            Action::make('cancel')
                ->label('Cancelar')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];

        
    }
    
    protected function afterCreate(): void
    {
        $ar = $this->record;

        CollectionManagement::firstOrCreate(
            ['account_receivable_id' => $ar->id],
            [
                'customer_id' => $ar->customer_id,
                'next_reminder_at' => null,
                'reminder_attempts' => 0,
                'last_action' => null,
                'notes' => null,
            ]
        );
    }

}