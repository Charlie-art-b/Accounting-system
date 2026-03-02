<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Resources\Suppliers\SupplierResource;
use App\Services\PdfFallbackService;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewSupplier extends ViewRecord
{
    protected static string $resource = SupplierResource::class;
 protected static ?string $title = 'Detalles del proveedor';
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
                ->visible(fn () => auth()->user()?->can('suppliers.view'))
                ->action(fn () => app(PdfFallbackService::class)->download(
                    view: 'exports.generic-model-pdf',
                    data: [
                        'title' => 'Proveedor',
                        'fields' => ['tipo_proveedor', 'nombre_razon_social', 'identificacion', 'correo', 'telefono', 'estado'],
                        'displayFields' => ['Tipo de Proveedor', 'Nombre / Razón Social', 'Identificación', 'Correo Electrónico', 'Teléfono', 'Estado'],
                        'records' => collect([$this->record]),
                    ],
                    baseFileName: 'proveedor_' . $this->record->id . '_' . now()->format('Y-m-d_H-i-s'),
                    paper: 'a4',
                    orientation: 'landscape',
                )),

            EditAction::make()
                ->visible(fn () => auth()->user()?->can('suppliers.update')),
        ];
    }
}