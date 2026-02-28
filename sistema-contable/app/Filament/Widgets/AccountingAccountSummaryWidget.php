<?php

namespace App\Filament\Widgets;

use App\Models\AccountingAccount;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AccountingAccountSummaryWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected int | array | null $columns = 4;

    protected function getStats(): array
    {
        return [
            Stat::make('Cuentas', number_format(AccountingAccount::query()->count()))->color('primary'),
            Stat::make('Activas', number_format(AccountingAccount::query()->where('status', 'Activa')->count()))->color('success'),
            Stat::make('Deudoras', number_format(AccountingAccount::query()->where('normal_balance', 'debit')->count()))->color('info'),
            Stat::make('Acreedoras', number_format(AccountingAccount::query()->where('normal_balance', 'credit')->count()))->color('warning'),
        ];
    }
}

