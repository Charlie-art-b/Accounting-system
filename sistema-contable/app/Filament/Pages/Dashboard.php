<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AccountsTrendChartWidget;
use App\Filament\Widgets\BusinessOverviewWidget;
use App\Filament\Widgets\PendingDocumentsWidget;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\WeeklyRevenueChartWidget;
use App\Filament\Widgets\WelcomeWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getTitle(): string
    {
        return 'Escritorio';
    }

    public function getSubheading(): ?string
    {
        return 'Resumen general de tu empresa';
    }

    public function getColumns(): int | array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }

    public function getWidgets(): array
    {
        return [
            WelcomeWidget::class,
            BusinessOverviewWidget::class,
            AccountsTrendChartWidget::class,
            WeeklyRevenueChartWidget::class,
            RecentActivityWidget::class,
            QuickActionsWidget::class,
            PendingDocumentsWidget::class,
        ];
    }
}

