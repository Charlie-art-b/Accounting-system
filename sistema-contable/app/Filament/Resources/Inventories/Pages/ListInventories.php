<?php

namespace App\Filament\Resources\Inventories\Pages;

use App\Filament\Resources\Inventories\InventoryResource;
use App\Filament\Widgets\InventorySummaryWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use App\Filament\Resources\Products\ProductResource;

class ListInventories extends ListRecords
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            InventorySummaryWidget::class,
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
                ->label('Crear Inventario')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->keyBindings(['mod+n'])
                ->visible(fn () => auth()->user()?->can('inventories.create') ?? false),

            Action::make('productos')
                ->label('Catálogo de Productos')
                ->icon('heroicon-o-cube')
                ->url(ProductResource::getUrl('index'))
                ->visible(fn () => auth()->user()?->can('products.view') ?? false),
        ];
    }
}
