<?php

namespace App\Filament\Resources\Inventories\Pages;

use App\Filament\Resources\Inventories\InventoryResource;
use App\Services\PdfFallbackService;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewInventory extends ViewRecord
{
    protected static string $resource = InventoryResource::class;
    protected static ?string $title = 'Detalles del inventario';
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
                ->visible(fn () => auth()->user()?->can('inventories.view'))
                ->action(function () {
                    $inventory = $this->record->load(['customer', 'inventoryProducts.product']);
                    
                    return app(PdfFallbackService::class)->download(
                        view: 'exports.inventory-with-products-pdf',
                        data: [
                            'inventory' => $inventory,
                        ],
                        baseFileName: 'inventario_' . $this->record->name . '_' . now()->format('Y-m-d_H-i-s'),
                        paper: 'a4',
                        orientation: 'landscape',
                    );
                }),

            EditAction::make()
                ->visible(fn () => auth()->user()?->can('inventories.update')),
        ];
    }
}
