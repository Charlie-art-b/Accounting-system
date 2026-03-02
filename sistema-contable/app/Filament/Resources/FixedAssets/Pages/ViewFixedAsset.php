<?php

namespace App\Filament\Resources\FixedAssets\Pages;

use App\Filament\Resources\FixedAssets\FixedAssetResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewFixedAsset extends ViewRecord
{
    protected static string $resource = FixedAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Volver a la lista')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),

            EditAction::make()
                ->visible(fn () => auth()->user()?->can('fixed_assets.update')),
        ];
    }
}
