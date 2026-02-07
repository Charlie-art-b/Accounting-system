<?php

namespace App\Filament\Resources\AccountReceivables\Pages;

use App\Filament\Resources\AccountReceivables\AccountReceivableResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewAccountReceivable extends ViewRecord
{
    protected static string $resource = AccountReceivableResource::class;
    protected static ?string $title = 'Detalles de la cuenta por cobrar';
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Editar')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->keyBindings(['mod+e']),7

        ];
    }
}