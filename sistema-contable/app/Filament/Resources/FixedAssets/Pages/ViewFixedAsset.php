<?php

namespace App\Filament\Resources\FixedAssets\Pages;

use App\Filament\Resources\FixedAssets\FixedAssetResource;
use App\Services\PdfFallbackService;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewFixedAsset extends ViewRecord
{
    protected static string $resource = FixedAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Volver a la lista')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),

            Action::make('export_pdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('warning')
                ->visible(fn () => auth()->user()?->can('fixed_assets.view'))
                ->action(fn () => app(PdfFallbackService::class)->download(
                    view: 'exports.generic-model-pdf',
                    data: [
                        'title' => 'Activo Fijo',
                        'fields' => ['asset_name', 'description', 'acquisition_value', 'acquisition_date', 'useful_life_years', 'residual_value', 'accumulated_depreciation', 'status', 'disposal_date', 'disposal_reason'],
                        'displayFields' => ['Nombre del Activo', 'Descripción', 'Valor de Adquisición', 'Fecha de Adquisición', 'Vida Útil', 'Valor Residual', 'Depreciación Acumulada', 'Estado', 'Fecha de Baja', 'Motivo de Baja'],
                        'records' => collect([$this->record]),
                    ],
                    baseFileName: 'activo_fijo_' . $this->record->id . '_' . now()->format('Y-m-d_H-i-s'),
                    paper: 'a4',
                    orientation: 'landscape',
                )),

            EditAction::make()
                ->visible(fn () => auth()->user()?->can('fixed_assets.update')),
        ];
    }
}
