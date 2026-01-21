<?php

namespace App\Filament\Resources;

use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationLabel = 'Proveedores';

    protected static ?string $modelLabel = 'Proveedor';

    protected static ?string $pluralModelLabel = 'Proveedores';

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Proveedor')
                    ->description('Registra los datos básicos del proveedor')
                    ->schema([
                        Forms\Components\Select::make('tipo_proveedor')
                            ->label('Tipo de Proveedor')
                            ->options(Supplier::$tiposProveedor)
                            ->required('El tipo de proveedor es obligatorio')
                            ->native(false)
                            ->searchable()
                            ->hint('Selecciona si es Persona o Empresa')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('nombre_razon_social')
                            ->label('Nombre o Razón Social')
                            ->required('El nombre o razón social es obligatorio')
                            ->minLength(3, 'El nombre debe tener al menos 3 caracteres')
                            ->maxLength(255, 'El nombre no puede exceder 255 caracteres')
                            ->regex('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.\-\&]+$/', 'El nombre solo puede contener letras, espacios, puntos, guiones y &')
                            ->hint('Mínimo 3 caracteres, máximo 255')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('identificacion')
                            ->label('Identificación (Cédula/RUC/Pasaporte)')
                            ->required('La identificación es obligatoria')
                            ->unique('suppliers', 'identificacion', ignoreRecord: true)
                            ->regex('/^[0-9\-]+$/', 'La identificación solo puede contener números y guiones')
                            ->minLength(6, 'La identificación debe tener al menos 6 caracteres')
                            ->maxLength(50, 'La identificación no puede exceder 50 caracteres')
                            ->hint('Ej: 208-450-123 o 3-101-234567')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('correo')
                            ->label('Correo Electrónico')
                            ->email('El correo electrónico debe ser válido')
                            ->required('El correo es obligatorio')
                            ->unique('suppliers', 'correo', ignoreRecord: true)
                            ->maxLength(255, 'El correo no puede exceder 255 caracteres')
                            ->hint('Ej: contacto@empresa.cr')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('telefono')
                            ->label('Teléfono')
                            ->tel('El teléfono debe ser un número válido')
                            ->regex('/^[0-9\+\-\(\)\s]+$/', 'El teléfono solo puede contener números, +, -, paréntesis y espacios')
                            ->maxLength(20, 'El teléfono no puede exceder 20 caracteres')
                            ->hint('Ej: 2234-5678 o +506 2234 5678 (Opcional)')
                            ->columnSpan(1),

                        Forms\Components\Select::make('estado')
                            ->label('Estado')
                            ->options(Supplier::$estados)
                            ->default('activo')
                            ->required('El estado es obligatorio')
                            ->native(false)
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tipo_proveedor')
                    ->label('Tipo')
                    ->formatStateUsing(fn(string $state): string => Supplier::$tiposProveedor[$state] ?? $state)
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'persona' => 'info',
                        'empresa' => 'success',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('nombre_razon_social')
                    ->label('Nombre o Razón Social')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('identificacion')
                    ->label('Identificación')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('correo')
                    ->label('Correo')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->color(fn(string $state): string => match ($state) {
                        'activo' => 'success',
                        'inactivo' => 'danger',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(Supplier::$estados),

                Tables\Filters\SelectFilter::make('tipo_proveedor')
                    ->label('Tipo de Proveedor')
                    ->options(Supplier::$tiposProveedor),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nombre_razon_social', 'asc')
            ->paginated([15, 25, 50, 100]);
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
            'index' => \App\Filament\Resources\SupplierResource\Pages\ListSuppliers::route('/'),
            'create' => \App\Filament\Resources\SupplierResource\Pages\CreateSupplier::route('/create'),
            'edit' => \App\Filament\Resources\SupplierResource\Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
