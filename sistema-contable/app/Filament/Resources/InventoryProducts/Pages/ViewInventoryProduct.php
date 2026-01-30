<?php

namespace App\Filament\Resources\InventoryProducts\Pages;

use App\Filament\Resources\InventoryProducts\InventoryProductResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewInventoryProduct extends ViewRecord
{
    protected static string $resource = InventoryProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            Action::make('back')
                ->label('')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')) 
                ->tooltip('Volver'),
        ];
    }
}
