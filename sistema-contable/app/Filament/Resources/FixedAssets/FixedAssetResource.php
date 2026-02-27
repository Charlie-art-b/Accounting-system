<?php

namespace App\Filament\Resources\FixedAssets;

use App\Filament\Resources\FixedAssets\Pages\CreateFixedAsset;
use App\Filament\Resources\FixedAssets\Pages\EditFixedAsset;
use App\Filament\Resources\FixedAssets\Pages\ListFixedAssets;
use App\Filament\Resources\FixedAssets\Pages\ViewFixedAsset;
use App\Filament\Resources\FixedAssets\Schemas\FixedAssetForm;
use App\Filament\Resources\FixedAssets\Schemas\FixedAssetInfolist;
use App\Filament\Resources\FixedAssets\Tables\FixedAssetsTable;
use App\Models\FixedAsset;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FixedAssetResource extends Resource
{
    protected static ?string $model = FixedAsset::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $recordTitleAttribute = 'Activo fijo';

    protected static string|\UnitEnum|null $navigationGroup = 'PRINCIPAL';

    protected static ?int $navigationSort = 8;


    public static function canViewAny(): bool
    {
        return auth()->user()?->can('fixed_assets.view') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('fixed_assets.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('fixed_assets.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('fixed_assets.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('fixed_assets.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('fixed_assets.delete') ?? false;
    }


    public static function getNavigationLabel(): string
    {
        return 'Gestión de Activos Fijos';
    }

    public static function getModelLabel(): string
    {
        return 'Activo fijo';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Activos fijos';
    }


    public static function form(Schema $schema): Schema
    {
        return FixedAssetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FixedAssetsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FixedAssetInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [];
    }


    public static function getPages(): array
    {
        return [
            'index' => ListFixedAssets::route('/'),
            'create' => CreateFixedAsset::route('/create'),
            'edit' => EditFixedAsset::route('/{record}/edit'),
            'view' => ViewFixedAsset::route('/{record}'),
        ];
    }
}