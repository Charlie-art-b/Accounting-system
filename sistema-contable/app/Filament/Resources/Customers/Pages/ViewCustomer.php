<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->url($this->getResource()::getUrl('index'))
                ->tooltip('Volver a la lista'),
            EditAction::make(),
        ];
    }
}
