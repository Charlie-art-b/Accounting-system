<?php

namespace App\Filament\Resources\JournalEntries\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn; 
use App\Models\JournalEntry;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;


class JournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Código de asiento')
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('journal_type')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),

                TextColumn::make('reference')
                    ->label('Referencia')
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('total_debit')
                    ->label('Débitos')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->money('CRC'),

                TextColumn::make('total_credit')
                    ->label('Créditos')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->money('CRC'),

                TextColumn::make('posted_at')
                    ->label('Posteado')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('BORRADOR'),

                TextColumn::make('postedBy.name')
                    ->label('Posteado por')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('posted_at')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Posteados')
                    ->falseLabel('Borradores')
                    ->queries(
                        true: fn ($q) => $q->whereNotNull('posted_at'),
                        false: fn ($q) => $q->whereNull('posted_at'),
                        blank: fn ($q) => $q,
                    ),

                SelectFilter::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make(),

                EditAction::make()
                    ->visible(fn ($record) => $record->posted_at === null),

                DeleteAction::make()
                    ->visible(fn ($record) => $record->posted_at === null),
            ])
            ->defaultSort('id', 'desc');
    }
}
