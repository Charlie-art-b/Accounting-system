<?php

namespace App\Filament\Widgets;

use App\Models\AccountPayable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AccountPayableSummaryWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected int | array | null $columns = 4;

    protected function getStats(): array
    {
        $pendingQuery = AccountPayable::query()->whereIn('status', ['pending', 'partial']);
        $pendingAmount = (float) $pendingQuery->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as total')->value('total');
        $overdueCount = AccountPayable::query()
            ->whereIn('status', ['pending', 'partial'])
            ->whereDate('due_date', '<', now()->toDateString())
            ->count();

        return [
            Stat::make('Total CxP', number_format(AccountPayable::query()->count()))->color('primary'),
            Stat::make('Pendientes', number_format($pendingQuery->count()))->color('warning'),
            Stat::make('Saldo pendiente', 'CRC ' . number_format($pendingAmount, 0, ',', '.'))->color('info'),
            Stat::make('Vencidas', number_format($overdueCount))->color('danger'),
        ];
    }
}

