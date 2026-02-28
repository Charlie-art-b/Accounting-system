<?php

namespace App\Filament\Widgets;

use App\Models\Supplier;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SupplierSummaryWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected int | array | null $columns = 4;

    protected function getStats(): array
    {
        return [
            Stat::make('Total proveedores', number_format(Supplier::query()->count()))->color('primary'),
            Stat::make('Activos', number_format(Supplier::query()->where('estado', 'activo')->count()))->color('success'),
            Stat::make('Empresas', number_format(Supplier::query()->where('tipo_proveedor', 'empresa')->count()))->color('info'),
            Stat::make('Con clientes', number_format(Supplier::query()->has('customers')->count()))->color('warning'),
        ];
    }
}

