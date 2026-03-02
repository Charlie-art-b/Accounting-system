<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
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

            EditAction::make()
                ->visible(fn () => auth()->user()?->can('products.update')),
        ];
    }
}
