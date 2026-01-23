<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')//orden alfabetico por nombre de cliente
            ->columns([
                TextColumn::make('name')
                    ->label(__('name'))
                    ->sortable()//para ordenar la columna
                    ->searchable(),
                TextColumn::make('first_last_name')
                    ->label(__('first_last_name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('second_last_name')
                    ->label(__('second_last_name'))
                    ->searchable(),
                TextColumn::make('id_type')
                    ->label(__('id_type'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'identification' => 'Cédula',
                        'dimex' => 'DIMEX',
                        'passport' => 'Pasaporte',
                        default => $state,
                    })
                    ->searchable(),
                TextColumn::make('identification')
                    ->label(__('identification'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('email'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('phone'))
                    ->searchable(),
                TextColumn::make('address')
                    ->label(__('address'))
                    ->searchable(),
                TextColumn::make('customer_type')
                    ->label(__('customer_type'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'individual' => 'Persona física',
                        'legal_person' => 'Persona jurídica',
                        default => $state,
                    }),
                IconColumn::make('status')
                    ->label(__('status'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
