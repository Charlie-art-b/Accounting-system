<?php

namespace App\Filament\Resources\AccountingAccounts;

use App\Filament\Resources\AccountingAccounts\Pages\CreateAccountingAccount;
use App\Filament\Resources\AccountingAccounts\Pages\EditAccountingAccount;
use App\Filament\Resources\AccountingAccounts\Pages\ListAccountingAccounts;
use App\Filament\Resources\AccountingAccounts\Schemas\AccountingAccountForm;
use App\Filament\Resources\AccountingAccounts\Tables\AccountingAccountsTable;
use App\Models\AccountingAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


class AccountingAccountResource extends Resource
{
    protected static ?string $model = AccountingAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AccountingAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountingAccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccountingAccounts::route('/'),
            'create' => CreateAccountingAccount::route('/create'),
            'edit' => EditAccountingAccount::route('/{record}/edit'),
        ];
    }
}
