<?php

namespace App\Filament\Resources\AccountPayables\Pages;

use App\Filament\Resources\AccountPayables\AccountPayableResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewAccountPayable extends ViewRecord
{
    protected static string $resource = AccountPayableResource::class;

    public function getTitle(): string
    {
        return "Cuenta por Pagar #{$this->record->document_number}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Volver a la lista')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
            
            EditAction::make()
                ->visible(fn () => $this->record->status !== 'paid'),
        ];
    }
}
