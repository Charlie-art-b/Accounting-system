<?php

namespace App\Filament\Resources\FixedAssets\Pages;

use App\Filament\Resources\FixedAssets\FixedAssetResource;
use App\Filament\Widgets\FixedAssetSummaryWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListFixedAssets extends ListRecords
{
    protected static string $resource = FixedAssetResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            FixedAssetSummaryWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        return [
        
            CreateAction::make()
                ->label('Registrar Activo Fijo')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->visible(fn () => auth()->user()?->can('fixed_assets.create') ?? false)
                ->url($this->getResource()::getUrl('create')),
        ];
    }
    
}
