<?php

namespace App\Filament\Resources\InventoryProducts;

use App\Filament\Resources\InventoryProducts\Pages\CreateInventoryProduct;
use App\Filament\Resources\InventoryProducts\Pages\EditInventoryProduct;
use App\Filament\Resources\InventoryProducts\Pages\ListInventoryProducts;
use App\Filament\Resources\InventoryProducts\Pages\ViewInventoryProduct;
use App\Filament\Resources\InventoryProducts\Schemas\InventoryProductForm;
use App\Filament\Resources\InventoryProducts\Schemas\InventoryProductInfolist;
use App\Filament\Resources\InventoryProducts\Tables\InventoryProductsTable;
use App\Models\InventoryProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class InventoryProductResource extends Resource
{
    protected static ?string $model = InventoryProduct::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;


    public static function getNavigationLabel(): string
    {
        return 'Existencias en Inventarios';
    }

    public static function getModelLabel(): string
    {
        return 'Existencias de productos';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Existencias';
    }

    // Permisos
    public static function canViewAny(): bool
    {
        return Auth::user()?->can('inventory_products.view') ?? false;
    }

    public static function canView($record): bool
    {
        return Auth::user()?->can('inventory_products.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('inventory_products.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->can('inventory_products.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->can('inventory_products.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return Auth::user()?->can('inventory_products.delete') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return InventoryProductForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InventoryProductInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventoryProductsTable::configure($table, [
            'canView' => self::canViewAny(),
            'canEdit' => self::canEdit(null),
            'canDelete' => self::canDelete(null),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInventoryProducts::route('/'),
            'create' => CreateInventoryProduct::route('/create'),
            'view' => ViewInventoryProduct::route('/{record}'),
            'edit' => EditInventoryProduct::route('/{record}/edit'),
        ];
    }
}