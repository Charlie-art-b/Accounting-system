<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')//orden alfabetico por nombre de cliente
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->sortable()//para ordenar la columna
                    ->searchable(),

                TextColumn::make('first_last_name')
                    ->label('Primer apellido')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('second_last_name')
                    ->label('Segundo apellido')
                    ->searchable(),

                TextColumn::make('id_type')
                    ->label('Tipo de identificación')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'identification' => 'Cédula',
                        'dimex' => 'DIMEX',
                        'passport' => 'Pasaporte',
                        default => $state,
                    })
                    ->searchable(),

                TextColumn::make('identification')
                    ->label('Identificación')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),

                TextColumn::make('address')
                    ->label('Dirección')
                    ->searchable(),

                TextColumn::make('customer_type')
                    ->label('Tipo de cliente')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'individual' => 'Persona física',
                        'legal_person' => 'Persona jurídica',
                        default => $state,
                    }),

                IconColumn::make('status')
                    ->label('Estado')
                    ->boolean(),

                TextColumn::make('suppliers_count')
                    ->label('Proveedores')
                    ->counts('suppliers')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Creado en')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado en')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('customer_type')
                    ->label('Tipo de cliente')
                    ->options([
                        'individual' => 'Persona física',
                        'legal_person' => 'Persona jurídica',
                    ]),

                TernaryFilter::make('status')
                    ->label('Estado')
                    ->trueLabel('Activo')
                    ->falseLabel('Inactivo'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
