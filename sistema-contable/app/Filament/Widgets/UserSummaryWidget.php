<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserSummaryWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected int | array | null $columns = 4;

    protected function getStats(): array
    {
        return [
            Stat::make('Usuarios', number_format(User::query()->count()))->color('primary'),
            Stat::make('Administradores', number_format(User::role('administrador')->count()))->color('danger'),
            Stat::make('Con rol asignado', number_format(User::has('roles')->count()))->color('success'),
            Stat::make('Nuevos este mes', number_format(User::query()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count()))->color('info'),
        ];
    }
}

