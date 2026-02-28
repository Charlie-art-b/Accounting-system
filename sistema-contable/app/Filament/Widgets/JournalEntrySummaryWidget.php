<?php

namespace App\Filament\Widgets;

use App\Models\JournalEntry;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class JournalEntrySummaryWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected int | array | null $columns = 4;

    protected function getStats(): array
    {
        return [
            Stat::make('Asientos', number_format(JournalEntry::query()->count()))->color('primary'),
            Stat::make('Posteados', number_format(JournalEntry::query()->whereNotNull('posted_at')->count()))->color('success'),
            Stat::make('Borradores', number_format(JournalEntry::query()->whereNull('posted_at')->count()))->color('warning'),
            Stat::make('Este mes', number_format(JournalEntry::query()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count()))->color('info'),
        ];
    }
}

