<?php

namespace App\Filament\Resources\AccountReceivables\Pages;

use App\Filament\Resources\AccountReceivables\AccountReceivableResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewAccountReceivable extends ViewRecord
{
    protected static string $resource = AccountReceivableResource::class;
    protected static ?string $title = 'Detalles de la cuenta por cobrar';
    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Volver a la lista')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
                
            EditAction::make()
                ->label('Editar')
                ->color('primary')
                ->keyBindings(['mod+e'])
                ->visible(fn () => $this->record->status !== 'paid'),
        ];
    }
}