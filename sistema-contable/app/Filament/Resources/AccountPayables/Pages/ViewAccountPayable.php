<?php

namespace App\Filament\Resources\AccountPayables\Pages;

use App\Filament\Resources\AccountPayables\AccountPayableResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAccountPayable extends ViewRecord
{
    protected static string $resource = AccountPayableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
