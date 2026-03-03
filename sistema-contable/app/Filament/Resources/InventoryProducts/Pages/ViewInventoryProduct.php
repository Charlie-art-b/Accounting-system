<?php

namespace App\Filament\Resources\InventoryProducts\Pages;

use App\Filament\Resources\InventoryProducts\InventoryProductResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewInventoryProduct extends ViewRecord
{
    protected static string $resource = InventoryProductResource::class;
    protected static ?string $title = 'Detalles de las existencias del producto';
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => auth()->user()?->can('inventory_products.update')),

            Action::make('back')
                ->label('')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')) 
                ->tooltip('Volver'),
        ];
    }
}
