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
                ->label('Volver al historial')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}