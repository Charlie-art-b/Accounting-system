<?php

namespace App\Filament\Widgets;

use App\Models\InventoryProduct;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryProductSummaryWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected int | array | null $columns = 4;

    protected function getStats(): array
    {
        $existence = (int) InventoryProduct::query()->selectRaw('COALESCE(SUM(stock_initial + entries - exits), 0) as total')->value('total');
        $lowStock = InventoryProduct::query()->whereRaw('(stock_initial + entries - exits) < 10')->count();
        $distinctProducts = InventoryProduct::query()->distinct('product_id')->count('product_id');

        return [
            Stat::make('Registros', number_format(InventoryProduct::query()->count()))->color('primary'),
            Stat::make('Productos distintos', number_format($distinctProducts))->color('info'),
            Stat::make('Existencias', number_format($existence))->color('success'),
            Stat::make('Stock bajo', number_format($lowStock))->color('warning'),
        ];
    }
}

