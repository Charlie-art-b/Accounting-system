<?php

namespace App\Filament\Widgets;

use App\Models\AccountPayable;
use App\Models\AccountReceivable;
use App\Models\Customer;
use App\Models\FixedAsset;
use App\Models\Inventory;
use App\Models\InventoryProduct;
use App\Models\Product;
use App\Models\Supplier;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BusinessOverviewWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $receivablesOpenQuery = AccountReceivable::query()
            ->whereIn('status', ['pending', 'partial']);

        $payablesOpenQuery = AccountPayable::query()
            ->whereIn('status', ['pending', 'partial']);

        $receivablesOpenTotal = (float) $receivablesOpenQuery
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as total')
            ->value('total');

        $payablesOpenTotal = (float) $payablesOpenQuery
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as total')
            ->value('total');

        $inventoryUnits = (int) InventoryProduct::query()
            ->selectRaw('COALESCE(SUM(stock_initial + entries - exits), 0) as units')
            ->value('units');

        $fixedAssetsNetValue = (float) FixedAsset::query()
            ->where('status', 'active')
            ->selectRaw('COALESCE(SUM(acquisition_value - accumulated_depreciation), 0) as total')
            ->value('total');

        return [
            Stat::make('Clientes activos', number_format(Customer::query()->where('status', true)->count()))
                ->description('Total de clientes habilitados')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Proveedores activos', number_format(Supplier::query()->where('estado', 'activo')->count()))
                ->description('Total de proveedores habilitados')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('info'),

            Stat::make('CxC pendientes', $this->formatCurrency($receivablesOpenTotal))
                ->description(number_format($receivablesOpenQuery->count()) . ' documentos por cobrar')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),

            Stat::make('CxP pendientes', $this->formatCurrency($payablesOpenTotal))
                ->description(number_format($payablesOpenQuery->count()) . ' documentos por pagar')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Inventarios y productos', number_format(Inventory::query()->count()) . ' / ' . number_format(Product::query()->count()))
                ->description(number_format($inventoryUnits) . ' unidades totales en stock')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),

            Stat::make('Activos fijos activos', number_format(FixedAsset::query()->where('status', 'active')->count()))
                ->description('Valor neto: ' . $this->formatCurrency($fixedAssetsNetValue))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('gray'),
        ];
    }

    private function formatCurrency(float $value): string
    {
        return 'CRC ' . number_format($value, 2, ',', '.');
    }
}
