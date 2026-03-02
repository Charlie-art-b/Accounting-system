<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;
    protected static ?string $title = 'Detalles del cliente';
    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Volver a la lista')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),

            EditAction::make()
                ->visible(fn () => auth()->user()?->can('customers.update')),
        ];
    }
}
