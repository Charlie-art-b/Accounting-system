<?php

namespace App\Filament\Resources\InventoryProducts\Pages;

use App\Filament\Resources\Inventories\InventoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\InventoryProducts\InventoryProductResource;
use Filament\Actions\Action;

class ListInventoryProducts extends ListRecords
{
    protected static string $resource = InventoryProductResource::class;
    protected static ?string $title = 'Existencias';

    protected function getHeaderActions(): array
    {
        return [
          CreateAction::make()
                ->label('Agregar Existencias'),
                
             Action::make('back')
                ->label('')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->url(InventoryResource::getUrl('index')) 
                ->tooltip('Volver'),
        ];
    }
}
