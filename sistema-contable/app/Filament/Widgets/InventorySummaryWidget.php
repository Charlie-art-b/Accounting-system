<?php

namespace App\Filament\Widgets;

use App\Models\Inventory;
use App\Models\InventoryProduct;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventorySummaryWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected int | array | null $columns = 4;

    protected function getStats(): array
    {
        $units = (int) InventoryProduct::query()->selectRaw('COALESCE(SUM(stock_initial + entries - exits), 0) as total')->value('total');
        $lowStock = InventoryProduct::query()->whereRaw('(stock_initial + entries - exits) < 10')->count();

        return [
            Stat::make('Inventarios', number_format(Inventory::query()->count()))->color('primary'),
            Stat::make('Productos enlazados', number_format(InventoryProduct::query()->count()))->color('info'),
            Stat::make('Unidades en stock', number_format($units))->color('success'),
            Stat::make('Stock bajo', number_format($lowStock))->color('warning'),
        ];
    }
}

