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

class InventoryProductResource extends Resource
{
    protected static ?string $model = InventoryProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'inventory_id';

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
        return InventoryProductsTable::configure($table);
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
            'index' => ListInventoryProducts::route('/'),
            'create' => CreateInventoryProduct::route('/create'),
            'view' => ViewInventoryProduct::route('/{record}'),
            'edit' => EditInventoryProduct::route('/{record}/edit'),
        ];
    }
}
