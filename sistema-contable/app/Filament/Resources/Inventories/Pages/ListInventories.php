<?php

namespace App\Filament\Resources\Inventories\Pages;

use App\Filament\Resources\Inventories\InventoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use App\Filament\Resources\Products\ProductResource;

class ListInventories extends ListRecords
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Inventario')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->keyBindings(['mod+n']),

            Action::make('productos')
                ->label('Catálogo de Productos')
                ->icon('heroicon-o-cube')
                ->url(ProductResource::getUrl('index')),
        ];
    }
}
