<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerSummaryWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected int | array | null $columns = 4;

    protected function getStats(): array
    {
        $total = Customer::query()->count();
        $individual = Customer::query()->where('customer_type', 'individual')->count();
        $legal = Customer::query()->where('customer_type', 'legal_person')->count();
        $newThisMonth = Customer::query()
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        return [
            Stat::make('Total clientes', number_format($total))
                ->description('Clientes registrados')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Persona fisica', number_format($individual))
                ->description('Clientes individuales')
                ->descriptionIcon('heroicon-m-user')
                ->color('info'),

            Stat::make('Persona juridica', number_format($legal))
                ->description('Clientes empresa')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('gray'),

            Stat::make('Nuevos este mes', number_format($newThisMonth))
                ->description('Altas del mes')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('success'),
        ];
    }
}

