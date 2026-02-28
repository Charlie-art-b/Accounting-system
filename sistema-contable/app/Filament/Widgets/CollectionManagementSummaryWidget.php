<?php

namespace App\Filament\Widgets;

use App\Models\CollectionManagement;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CollectionManagementSummaryWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected int | array | null $columns = 4;

    protected function getStats(): array
    {
        $overdue = CollectionManagement::query()
            ->whereHas('accountReceivable', fn ($q) => $q->whereDate('due_date', '<', now()->toDateString())->whereIn('status', ['pending', 'partial']))
            ->count();

        $dueSoon = CollectionManagement::query()
            ->whereHas('accountReceivable', fn ($q) => $q
                ->whereBetween('due_date', [now()->toDateString(), now()->addDays(3)->toDateString()])
                ->whereIn('status', ['pending', 'partial']))
            ->count();

        $attempts = (int) CollectionManagement::query()->sum('reminder_attempts');

        return [
            Stat::make('Gestiones', number_format(CollectionManagement::query()->count()))->color('primary'),
            Stat::make('Vencidas', number_format($overdue))->color('danger'),
            Stat::make('Proximas', number_format($dueSoon))->color('warning'),
            Stat::make('Intentos', number_format($attempts))->color('info'),
        ];
    }
}

