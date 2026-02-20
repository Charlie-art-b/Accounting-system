<?php

namespace App\Filament\Resources\AccountingAccounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Models\Customer;

class AccountingAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),

                // 🔥 NUEVA COLUMNA
                TextColumn::make('normal_balance')
                    ->label('Naturaleza')
                    ->badge()
                    ->formatStateUsing(fn ($state) =>
                        $state === 'debit' ? 'Deudora' : 'Acreedora'
                    )
                    ->color(fn ($state) =>
                        $state === 'debit' ? 'info' : 'warning'
                    ),

                // 🔥 SALDO DINÁMICO
                TextColumn::make('saldo')
                    ->label('Saldo')
                    ->getStateUsing(fn ($record) => $record->getSaldo())
                    ->money('USD', true),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state) =>
                        $state === 'Activa' ? 'success' : 'danger'
                    )
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

                SelectFilter::make('customer_id')
                    ->label('Cliente')
                    ->options(
                        Customer::orderBy('name')->pluck('name', 'id')->toArray()
                    ),

                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'Activo' => 'Activo',
                        'Pasivo' => 'Pasivo',
                        'Patrimonio' => 'Patrimonio',
                        'Ingreso' => 'Ingreso',
                        'Gasto' => 'Gasto',
                    ]),

                // 🔥 NUEVO FILTRO
                SelectFilter::make('normal_balance')
                    ->label('Naturaleza')
                    ->options([
                        'debit' => 'Deudora',
                        'credit' => 'Acreedora',
                    ]),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'Activa' => 'Activa',
                        'Inactiva' => 'Inactiva',
                    ]),
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