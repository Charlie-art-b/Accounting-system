<?php

namespace App\Filament\Resources\Inventories;

use App\Filament\Resources\Inventories\Pages\CreateInventory;
use App\Filament\Resources\Inventories\Pages\EditInventory;
use App\Filament\Resources\Inventories\Pages\ListInventories;
use App\Filament\Resources\Inventories\Pages\ViewInventory;
use App\Filament\Resources\Inventories\Schemas\InventoryForm;
use App\Filament\Resources\Inventories\Schemas\InventoryInfolist;
use App\Filament\Resources\Inventories\Tables\InventoriesTable;
use App\Models\Inventory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationLabel = 'Inventarios';

    protected static ?string $modelLabel = 'Inventario';

    protected static ?string $pluralModelLabel = 'Inventarios';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|\UnitEnum|null $navigationGroup = 'PRINCIPAL';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('inventories.view') ?? false;
    }

    public static function canView($record): bool
    {
        return Auth::user()?->can('inventories.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('inventories.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->can('inventories.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->can('inventories.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return Auth::user()?->can('inventories.delete') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return InventoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventoriesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InventoryInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\Inventories\RelationManagers\InventoryProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListInventories::route('/'),
            'create' => CreateInventory::route('/create'),
            'edit'   => EditInventory::route('/{record}/edit'),
            'view'   => ViewInventory::route('/{record}'),
        ];
    }
}