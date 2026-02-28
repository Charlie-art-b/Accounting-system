<?php

namespace App\Filament\Resources\FinancialReports\Pages;

use App\Filament\Resources\FinancialReports\FinancialReportResource;
use App\Filament\Widgets\FinancialReportSummaryWidget;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListFinancialReports extends ListRecords
{
    protected static string $resource = FinancialReportResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            FinancialReportSummaryWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    protected function getHeaderActions(): array
{
    return [
        Action::make('back')
            ->label('')
            ->icon('heroicon-o-x-mark')
            ->color('gray')
            ->tooltip('Volver')
            ->url(url('/admin/financial-reports')),
    ];
}
}
