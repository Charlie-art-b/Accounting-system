<?php

namespace App\Filament\Resources\AccountingAccounts\Pages;

use App\Filament\Resources\AccountingAccounts\AccountingAccountResource;
use App\Filament\Widgets\AccountingAccountSummaryWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccountingAccounts extends ListRecords
{
    protected static string $resource = AccountingAccountResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            AccountingAccountSummaryWidget::class,
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
                ->label('Crear cuenta contable')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->keyBindings(['mod+n'])
                ->visible(fn () => auth()->user()?->can('accounting_accounts.create')),
        ];
    }
}
