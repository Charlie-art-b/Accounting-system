<?php

namespace App\Filament\Resources\JournalEntries\Tables;

use App\Filament\Support\CrudImportExportActions;
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
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;


class JournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Nº de asiento')
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('journal_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'general' => 'General',
                        'adjustment' => 'Ajuste',
                        'closing' => 'Cierre',
                        'reversal' => 'Reverso',
                        default => $state,
                    })
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
                    ->preload(),

                SelectFilter::make('journal_type')
                    ->label('Tipo de asiento')
                    ->options([
                        'general' => 'General',
                        'adjustment' => 'Ajuste',
                        'closing' => 'Cierre',
                        'reversal' => 'Reverso',
                    ])
                    ->preload(),
                Filter::make('posted_date_range')
                    ->label('Fecha de posteo')
                    ->form([
                        DatePicker::make('from')->label('Desde'),
                        DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['from']) {
                            $query->whereDate('posted_at', '>=', $data['from']);
                        }
                        if ($data['until']) {
                            $query->whereDate('posted_at', '<=', $data['until']);
                        }
                    }),
                
            ])
            ->actions([
                ViewAction::make(),

                EditAction::make()
                    ->visible(fn ($record) => $record->posted_at === null),

                //DeleteAction::make()
                    //->visible(fn ($record) => $record->posted_at === null),
            ])
            ->toolbarActions([
                ...CrudImportExportActions::make(
                    modelClass: JournalEntry::class,
                    title: 'Asientos Contables',
                    filePrefix: 'asientos-contables',
                    fields: [
                        'id',
                        'customer_id',
                        'journal_type',
                        'entry_category',
                        'description',
                        'reference',
                        'total_debit',
                        'total_credit',
                        'posted_at',
                        'posted_by',
                        'is_reversal',
                        'reversed_entry_id',
                        'source_type',
                        'source_id',
                    ],
                    uniqueBy: ['id'],
                    defaults: [
                        'total_debit' => 0,
                        'total_credit' => 0,
                        'is_reversal' => false,
                    ],
                    enumMaps: [
                        'journal_type' => [
                            'general' => 'general',
                            'adjustment' => 'adjustment',
                            'ajuste' => 'adjustment',
                            'closing' => 'closing',
                            'cierre' => 'closing',
                            'reversal' => 'reversal',
                            'reverso' => 'reversal',
                        ],
                    ],
                ),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'asc');
    }
}
