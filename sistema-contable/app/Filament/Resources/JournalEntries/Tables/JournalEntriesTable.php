<?php

namespace App\Filament\Resources\JournalEntries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('journal_type')
                    ->label('Tipo de diario')
                    ->searchable(),
                TextColumn::make('reference')
                    ->label('Referencia')
                    ->searchable(),
                TextColumn::make('total_debit')
                    ->label('Débito total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_credit')
                    ->label('Crédito total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('fiscal_period_id')
                    ->label('Periodo fiscal')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('posted_at')
                    ->label('Fecha de publicación')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('posted_by')
                    ->label('Publicado por')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_reversal')
                    ->label('Es reversión')
                    ->boolean(),
                TextColumn::make('reversedEntry.id')
                    ->label('ID de entrada reversada')
                    ->searchable(),
                TextColumn::make('source_type')
                    ->label('Tipo de origen')
                    ->searchable(),
                TextColumn::make('source_id')
                    ->label('ID de origen')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Fecha de creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Fecha de actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                //ViewAction::make(),
                //EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
