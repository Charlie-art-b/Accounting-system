<?php

namespace App\Filament\Resources\CollectionManagement\Pages;

use App\Filament\Resources\CollectionManagement\CollectionManagementResource;
use App\Filament\Widgets\CollectionManagementSummaryWidget;
use Filament\Resources\Pages\ListRecords;

class ListCollectionManagement extends ListRecords
{
    protected static string $resource = CollectionManagementResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            CollectionManagementSummaryWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
