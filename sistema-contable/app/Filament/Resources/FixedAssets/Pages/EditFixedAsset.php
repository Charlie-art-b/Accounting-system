<?php

namespace App\Filament\Resources\FixedAssets\Pages;

use App\Filament\Resources\FixedAssets\FixedAssetResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditFixedAsset extends EditRecord
{
    protected static string $resource = FixedAssetResource::class;

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
    {
        return [    
            Action::make('save')
                ->label('Guardar')
                ->keyBindings(['mod+s'])
                ->requiresConfirmation()
                ->modalHeading('Confirmar actualización')
                ->modalDescription('¿Deseas guardar los cambios en este activo fijo? Revisa los datos antes de confirmar.')
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