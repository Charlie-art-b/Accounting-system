<?php

namespace App\Filament\Resources\AccountReceivables\Pages;

use App\Filament\Resources\AccountReceivables\AccountReceivableResource;
use App\Filament\Widgets\AccountReceivableSummaryWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListAccountReceivables extends ListRecords
{
    protected static string $resource = AccountReceivableResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            AccountReceivableSummaryWidget::class,
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
                ->label('Crear cuenta por cobrar')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->keyBindings(['mod+n'])
                ->visible(fn () => auth()->user()?->can('account_receivables.create')),
        ];
    }
}
