<?php

namespace App\Filament\Resources\CollectionManagement;

use App\Filament\Resources\CollectionManagement\Pages\ListCollectionManagement;
use App\Filament\Resources\CollectionManagement\Pages\ViewCollectionManagement;
use App\Filament\Resources\CollectionManagement\Schemas\CollectionManagementInfolist;
use App\Filament\Resources\CollectionManagement\Tables\CollectionManagementTable;
use App\Models\CollectionManagement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CollectionManagementResource extends Resource
{
    protected static ?string $model = CollectionManagement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|\UnitEnum|null $navigationGroup = 'PRINCIPAL';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return CollectionManagementForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CollectionManagementInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CollectionManagementTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return 'Gestión de cobros';
    }

    public static function getModelLabel(): string
    {
        return 'Gestión de cobros';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCollectionManagement::route('/'),
            'view' => ViewCollectionManagement::route('/{record}'),
        ];
    }
}

