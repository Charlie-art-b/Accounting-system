<?php

namespace App\Filament\Resources\AccountPayables;

use App\Filament\Resources\AccountPayables\Pages\CreateAccountPayable;
use App\Filament\Resources\AccountPayables\Pages\EditAccountPayable;
use App\Filament\Resources\AccountPayables\Pages\ListAccountPayables;
use App\Filament\Resources\AccountPayables\Pages\ViewAccountPayable;
use App\Filament\Resources\AccountPayables\Schemas\AccountPayableForm;
use App\Filament\Resources\AccountPayables\Schemas\AccountPayableInfolist;
use App\Filament\Resources\AccountPayables\Tables\AccountPayablesTable;
use App\Models\AccountPayable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AccountPayableResource extends Resource
{
    protected static ?string $model = AccountPayable::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'document_number';

    public static function form(Schema $schema): Schema
    {
        return AccountPayableForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AccountPayableInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountPayablesTable::configure($table);
    }

    public static function getPluralModelLabel(): string
    {
        return 'Cuentas por pagar';
    }

    public static function getModelLabel(): string
    {
        return 'Cuenta por pagar';
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\AccountPayables\RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccountPayables::route('/'),
            'create' => CreateAccountPayable::route('/create'),
            'view' => ViewAccountPayable::route('/{record}'),
            'edit' => EditAccountPayable::route('/{record}/edit'),
        ];
    }
}
