<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Services\PdfFallbackService;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

 protected static ?string $title = 'Detalles del producto';

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
                ->visible(fn () => auth()->user()?->can('products.view'))
                ->action(fn () => app(PdfFallbackService::class)->download(
                    view: 'exports.generic-model-pdf',
                    data: [
                        'title' => 'Producto',
                        'fields' => ['name', 'description', 'supplier.nombre_razon_social'],
                        'displayFields' => ['Nombre', 'Descripción', 'Proveedor'],
                        'records' => collect([$this->record]),
                    ],
                    baseFileName: 'producto_' . $this->record->id . '_' . now()->format('Y-m-d_H-i-s'),
                    paper: 'a4',
                    orientation: 'landscape',
                )),

            EditAction::make()
                ->visible(fn () => auth()->user()?->can('products.update')),
        ];
    }
}
