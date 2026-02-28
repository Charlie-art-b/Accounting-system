<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductSummaryWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected int | array | null $columns = 4;

    protected function getStats(): array
    {
        $withSupplier = Product::query()->whereNotNull('supplier_id')->count();
        $inInventory = Product::query()->has('inventoryProduct')->count();

        return [
            Stat::make('Productos', number_format(Product::query()->count()))->color('primary'),
            Stat::make('Con proveedor', number_format($withSupplier))->color('info'),
            Stat::make('En inventario', number_format($inInventory))->color('success'),
            Stat::make('Sin inventario', number_format(max(Product::query()->count() - $inInventory, 0)))->color('warning'),
        ];
    }
}

