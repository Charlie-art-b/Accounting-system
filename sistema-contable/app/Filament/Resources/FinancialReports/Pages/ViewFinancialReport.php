<?php

namespace App\Filament\Resources\FinancialReports\Pages;

use App\Filament\Resources\FinancialReports\FinancialReportResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewFinancialReport extends ViewRecord
{
    protected static string $resource = FinancialReportResource::class;

     public function getTitle(): string
    {
        return 'Detalle del Reporte #' . $this->record->id;
    }

     protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->tooltip('Volver')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}