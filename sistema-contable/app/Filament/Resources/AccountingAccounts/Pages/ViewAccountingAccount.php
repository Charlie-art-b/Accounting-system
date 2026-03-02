<?php

namespace App\Filament\Resources\AccountingAccounts\Pages;

use App\Filament\Resources\AccountingAccounts\AccountingAccountResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewAccountingAccount extends ViewRecord
{
    protected static string $resource = AccountingAccountResource::class;

    protected static ?string $title = 'Detalles de la Cuenta Contable';

    public function getTitle(): string
    {
        return $this->record->name ?? 'Detalles';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Volver a la lista')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),

            EditAction::make()
                ->label('Editar')
                ->visible(fn () => auth()->user()?->can('accounting_accounts.update')),
        ];
    }
}
