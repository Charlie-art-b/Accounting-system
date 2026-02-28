<?php

namespace App\Filament\Resources\InventoryProducts\Pages;

use App\Filament\Resources\Inventories\InventoryResource;
use App\Filament\Widgets\InventoryProductSummaryWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\InventoryProducts\InventoryProductResource;
use Filament\Actions\Action;

class ListInventoryProducts extends ListRecords
{
    protected static string $resource = InventoryProductResource::class;
    protected static ?string $title = 'Existencias';

    protected function getHeaderWidgets(): array
    {
        return [
            InventoryProductSummaryWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Agregar Producto al Inventario')
                ->icon('heroicon-o-plus')
                ->color('success')
                 ->visible(fn () => auth()->user()?->can('inventory_products.create') ?? false),

                
            Action::make('back')
                ->label('')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->url(InventoryResource::getUrl('index')) 
                ->tooltip('Volver'),
        ];
    }
}
