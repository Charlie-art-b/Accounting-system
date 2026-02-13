<?php

namespace App\Filament\Resources\CollectionManagement\Pages;

use App\Filament\Resources\CollectionManagement\CollectionManagementResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewCollectionManagement extends ViewRecord
{
    protected static string $resource = CollectionManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->url($this->getResource()::getUrl('index'))
                ->tooltip('Volver a la lista'),
        ];
    }
}
