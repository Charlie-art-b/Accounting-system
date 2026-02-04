<?php

namespace App\Filament\Resources\AccountReceivables;

use App\Filament\Resources\AccountReceivables\Pages\CreateAccountReceivable;
use App\Filament\Resources\AccountReceivables\Pages\EditAccountReceivable;
use App\Filament\Resources\AccountReceivables\Pages\ListAccountReceivables;
use App\Filament\Resources\AccountReceivables\Pages\ViewAccountReceivable;
use App\Filament\Resources\AccountReceivables\Schemas\AccountReceivableForm;
use App\Filament\Resources\AccountReceivables\Schemas\AccountReceivableInfolist;
use App\Filament\Resources\AccountReceivables\Tables\AccountReceivablesTable;
use App\Models\AccountReceivable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AccountReceivableResource extends Resource
{
    protected static ?string $model = AccountReceivable::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Cuentas por cobrar';

    public static function form(Schema $schema): Schema
    {
        return AccountReceivableForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AccountReceivableInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountReceivablesTable::configure($table);
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
            'index' => ListAccountReceivables::route('/'),
            //'create' => CreateAccountReceivable::route('/create'),
            //'view' => ViewAccountReceivable::route('/{record}'),
            //'edit' => EditAccountReceivable::route('/{record}/edit'),
        ];
    }
}
