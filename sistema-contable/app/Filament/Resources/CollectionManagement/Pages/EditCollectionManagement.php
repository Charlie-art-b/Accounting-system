<?php

namespace App\Filament\Resources\CollectionManagement\Pages;

use App\Filament\Resources\CollectionManagement\CollectionManagementResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCollectionManagement extends EditRecord
{
    protected static string $resource = CollectionManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
