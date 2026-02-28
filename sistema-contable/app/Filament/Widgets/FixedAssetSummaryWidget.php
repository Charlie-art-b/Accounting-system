<?php

namespace App\Filament\Widgets;

use App\Models\FixedAsset;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FixedAssetSummaryWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected int | array | null $columns = 4;

    protected function getStats(): array
    {
        $netValue = (float) FixedAsset::query()
            ->where('status', 'active')
            ->selectRaw('COALESCE(SUM(acquisition_value - accumulated_depreciation), 0) as total')
            ->value('total');

        return [
            Stat::make('Activos', number_format(FixedAsset::query()->count()))->color('primary'),
            Stat::make('Activos vigentes', number_format(FixedAsset::query()->where('status', 'active')->count()))->color('success'),
            Stat::make('Valor neto', 'CRC ' . number_format($netValue, 0, ',', '.'))->color('info'),
            Stat::make('Dados de baja', number_format(FixedAsset::query()->where('status', 'disposed')->count()))->color('warning'),
        ];
    }
}

