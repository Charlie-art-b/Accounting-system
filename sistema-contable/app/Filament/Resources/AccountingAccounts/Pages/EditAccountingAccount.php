<?php

namespace App\Filament\Resources\AccountingAccounts\Pages;

use App\Filament\Resources\AccountingAccounts\AccountingAccountResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAccountingAccount extends EditRecord
{
    protected static string $resource = AccountingAccountResource::class;

    protected function getRedirectUrl(): string
    {
        return AccountingAccountResource::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
