<?php

namespace App\Filament\Resources\Inventories\RelationManagers;

use App\Filament\Resources\Inventories\Pages\ViewInventory;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class InventoryProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'inventoryProducts';

    protected static ?string $title = 'Productos del Inventario';

    protected static ?string $modelLabel = 'producto';

    protected static ?string $pluralModelLabel = 'productos';

    protected static ?string $recordTitleAttribute = 'product.name';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if ($pageClass === ViewInventory::class) {
            return false;
        }

        return parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components($this->getFormSchemaComponents());
    }

    protected function getFormSchemaComponents(): array
    {
        return [
            Section::make('Producto')
                ->icon('heroicon-o-cube')
                ->columns(1)
                ->schema([
                    Select::make('product_id')
                        ->relationship('product', 'name')
                        ->label('Producto')
                        ->placeholder('Selecciona un producto')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->unique(
                            table: 'inventory_products',
                            column: 'product_id',
                            ignoreRecord: true,
                            modifyRuleUsing: fn ($rule) => $rule->where('inventory_id', $this->getOwnerRecord()->id)
                        )
                        ->columnSpanFull(),
                ]),

            Section::make('Cantidades')
                ->description('Gestiona el stock y movimientos')
                ->icon('heroicon-o-calculator')
                ->columns(3)
                ->schema([
                    TextInput::make('stock_initial')
                        ->label('Stock Inicial')
                        ->placeholder('0')
                        ->numeric()
                        ->integer()
                        ->step(1)
                        ->minValue(0)
                        ->default(0)
                        ->required()
                        ->dehydrateStateUsing(fn ($state) => (int) ($state ?? 0))
                        ->suffix('unidades'),

                    TextInput::make('entries')
                        ->label('Entradas')
                        ->placeholder('0')
                        ->numeric()
                        ->integer()
                        ->step(1)
                        ->minValue(0)
                        ->default(0)
                        ->required()
                        ->dehydrateStateUsing(fn ($state) => (int) ($state ?? 0))
                        ->suffix('unidades')
                        ->helperText('Productos que ingresan'),

                    TextInput::make('exits')
                        ->label('Salidas')
                        ->placeholder('0')
                        ->numeric()
                        ->integer()
                        ->step(1)
                        ->minValue(0)
                        ->default(0)
                        ->required()
                        ->dehydrateStateUsing(fn ($state) => (int) ($state ?? 0))
                        ->rule(fn (callable $get) => 'lte:' . max(0, (int) $get('stock_initial') + (int) $get('entries')))
                        ->validationMessages([
                            'lte' => 'Las salidas no pueden ser mayores que el stock inicial más las entradas.',
                        ])
                        ->suffix('unidades')
                        ->helperText('Productos que salen'),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('stock_initial')
                    ->label('Stock Inicial')
                    ->alignment('center')
                    ->badge()
                    ->color('gray')
                    ->suffix(' u'),

                TextColumn::make('entries')
                    ->label('Entradas')
                    ->alignment('center')
                    ->badge()
                    ->color('success')
                    ->suffix(' u'),

                TextColumn::make('exits')
                    ->label('Salidas')
                    ->alignment('center')
                    ->badge()
                    ->color('danger')
                    ->suffix(' u'),

                TextColumn::make('existence')
                    ->label('Existencia')
                    ->alignment('center')
                    ->state(fn ($record) => ($record->stock_initial + $record->entries - $record->exits))
                    ->badge()
                    ->color(fn ($record) => 
                        ($record->stock_initial + $record->entries - $record->exits) < 10 
                            ? 'warning' 
                            : 'success'
                    )
                    ->suffix(' u')
                    ->weight('bold')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('low_stock')
                    ->label('Stock bajo (< 10)')
                    ->query(fn ($query) => $query->whereRaw('(stock_initial + entries - exits) < 10')),
                
                Tables\Filters\Filter::make('no_stock')
                    ->label('Sin existencias')
                    ->query(fn ($query) => $query->whereRaw('(stock_initial + entries - exits) = 0')),
            ])
            ->headerActions([
                \Filament\Actions\Action::make('create')
                    ->label('Agregar Producto')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->form($this->getFormSchema())
                    ->action(function (array $data): void {
                        $this->getRelationship()->create($data);
                    })
                    ->modalHeading('Agregar producto al inventario')
                    ->successNotificationTitle('Producto agregado al inventario'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->label('Editar')
                    ->modalHeading(fn ($record) => "Editar '{$record->product->name}'")
                    ->successNotificationTitle('Producto actualizado'),

                \Filament\Actions\DeleteAction::make()
                    ->label('Quitar')
                    ->modalHeading(fn ($record) => "Quitar '{$record->product->name}' del inventario")
                    ->modalDescription('¿Estás seguro? Esto eliminará el producto de este inventario.')
                    ->successNotificationTitle('Producto removido del inventario'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()
                        ->modalHeading('Quitar productos del inventario')
                        ->modalDescription('¿Estás seguro de que deseas quitar estos productos del inventario?')
                        ->successNotificationTitle('Productos removidos del inventario'),
                ]),
            ])
            ->emptyStateHeading('Sin productos en este inventario')
            ->emptyStateDescription('Comienza agregando productos a este inventario.')
            ->emptyStateIcon('heroicon-o-cube')
            ->emptyStateActions([
                //
            ]);
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50];
    }

    protected function getFormSchema(): array
    {
        return $this->getFormSchemaComponents();
    }
}