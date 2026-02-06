<?php

namespace App\Filament\Resources\AccountPayables\Pages;

use App\Filament\Resources\AccountPayables\AccountPayableResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAccountPayable extends EditRecord
{
    protected static string $resource = AccountPayableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
