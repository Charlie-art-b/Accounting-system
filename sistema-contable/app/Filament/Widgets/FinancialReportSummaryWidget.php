<?php

namespace App\Filament\Widgets;

use App\Models\FinancialReport;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialReportSummaryWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected int | array | null $columns = 4;

    protected function getStats(): array
    {
        return [
            Stat::make('Reportes', number_format(FinancialReport::query()->count()))->color('primary'),
            Stat::make('Este mes', number_format(FinancialReport::query()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count()))->color('success'),
            Stat::make('Balance general', number_format(FinancialReport::query()->where('report_type', 'balance_general')->count()))->color('info'),
            Stat::make('Estado resultados', number_format(FinancialReport::query()->where('report_type', 'estado_resultados')->count()))->color('warning'),
        ];
    }
}

