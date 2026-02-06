<?php

namespace App\Filament\Resources\AccountReceivables\Pages;

use App\Filament\Resources\AccountReceivables\AccountReceivableResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditAccountReceivable extends EditRecord
{
    protected static string $resource = AccountReceivableResource::class;

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
            DeleteAction::make(),
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