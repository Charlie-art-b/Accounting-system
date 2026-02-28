<?php

namespace App\Filament\Resources\AccountPayables\Pages;

use App\Filament\Resources\AccountPayables\AccountPayableResource;
use App\Filament\Widgets\AccountPayableSummaryWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccountPayables extends ListRecords
{
    protected static string $resource = AccountPayableResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            AccountPayableSummaryWidget::class,
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
                ->label("Crear cuenta por pagar")
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->keyBindings(['mod+n'])
                ->visible(fn () => auth()->user()?->can('account_payables.create')),
        ];
    }
}
