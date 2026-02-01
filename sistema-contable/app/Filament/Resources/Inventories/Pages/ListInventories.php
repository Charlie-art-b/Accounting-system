<?php

namespace App\Filament\Resources\Inventories\Pages;

use App\Filament\Resources\Inventories\InventoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\InventoryProducts\InventoryProductResource;

class ListInventories extends ListRecords
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('productos')
                ->label('Catálogo de Productos')
                ->icon('heroicon-o-cube')
                ->url(ProductResource::getUrl('index')),

            Action::make('inventario productos')
                ->label('Ver Existencias en Inventarios')
                
                ->url(InventoryProductResource::getUrl('index')),

        ];
    }
}
