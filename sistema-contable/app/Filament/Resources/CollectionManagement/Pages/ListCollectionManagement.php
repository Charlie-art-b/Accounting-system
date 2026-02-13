<?php

namespace App\Filament\Resources\CollectionManagement\Pages;

use App\Filament\Resources\CollectionManagement\CollectionManagementResource;
use Filament\Resources\Pages\ListRecords;

class ListCollectionManagement extends ListRecords
{
    protected static string $resource = CollectionManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No se permite crear gestiones de cobros manualmente
        ];
    }
}
