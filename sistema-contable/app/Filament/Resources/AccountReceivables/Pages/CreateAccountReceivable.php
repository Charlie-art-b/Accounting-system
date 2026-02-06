<?php

namespace App\Filament\Resources\AccountReceivables\Pages;
use Filament\Actions\Action;

use App\Filament\Resources\AccountReceivables\AccountReceivableResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAccountReceivable extends CreateRecord
{
    protected static string $resource = AccountReceivableResource::class;
    
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
}