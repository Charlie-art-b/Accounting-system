<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Resources\Suppliers\SupplierResource;
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
                
            EditAction::make()
                ->visible(fn () => auth()->user()?->can('suppliers.update')),
        ];
    }
}