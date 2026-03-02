<?php

namespace App\Filament\Resources\AccountingAccounts;

use App\Filament\Resources\AccountingAccounts\Pages\CreateAccountingAccount;
use App\Filament\Resources\AccountingAccounts\Pages\EditAccountingAccount;
use App\Filament\Resources\AccountingAccounts\Pages\ListAccountingAccounts;
use App\Filament\Resources\AccountingAccounts\Pages\ViewAccountingAccount;
use App\Filament\Resources\AccountingAccounts\Schemas\AccountingAccountForm;
use App\Filament\Resources\AccountingAccounts\Schemas\AccountingAccountInfolist;
use App\Filament\Resources\AccountingAccounts\Tables\AccountingAccountsTable;
use App\Models\AccountingAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AccountingAccountResource extends Resource
{
    protected static ?string $model = AccountingAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static string|\UnitEnum|null $navigationGroup = 'PRINCIPAL';

    protected static ?int $navigationSort = 9;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getPluralModelLabel(): string
    {
        return 'Cuentas Contables';
    }

    public static function getModelLabel(): string
    {
        return 'Cuenta Contable';
    }

   
    public static function canViewAny(): bool
    {
        return Auth::user()?->can('accounting_accounts.view') ?? false;
    }

    public static function canView($record): bool
    {
        return Auth::user()?->can('accounting_accounts.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('accounting_accounts.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->can('accounting_accounts.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->can('accounting_accounts.delete') ?? false;
    }

    

    public static function form(Schema $schema): Schema
    {
        return AccountingAccountForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AccountingAccountInfolist::configure($schema);
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
            'view' => ViewAccountingAccount::route('/{record}'),
            'edit' => EditAccountingAccount::route('/{record}/edit'),
        ];
    }
}