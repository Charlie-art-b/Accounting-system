<?php

namespace App\Filament\Resources\JournalEntries\Tables;

use App\Filament\Support\CrudImportExportActions;
use App\Models\JournalEntry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class JournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'asc')
            ->columns([
                TextColumn::make('id')
                    ->label('Asiento')
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Descripcion')
                    ->limit(40)
                    ->searchable(),
                    //->toggleable(isToggledHiddenByDefault: true),
                    
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

                TextColumn::make('montos')
                    ->label('Montos')
                    ->state(fn (JournalEntry $record): string => number_format((float) $record->total_debit, 2, ',', '.') . "\n" . number_format((float) $record->total_credit, 2, ',', '.'))
                    ->formatStateUsing(function (string $state): HtmlString {
                        [$debit, $credit] = array_pad(explode("\n", $state, 2), 2, '0,00');

                        return new HtmlString(
                            "<div class='leading-tight'><span class='text-xs'>Debitos: CRC " . e($debit) . "</span><br><span class='text-xs fi-text-color-400'>Creditos: CRC " . e($credit) . '</span></div>'
                        );
                    })
                    ->html(),

                TextColumn::make('posted_at')
                    ->label('Posteado')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Borrador'),

                TextColumn::make('reference')
                    ->label('Referencia')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),


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
                        if ($data['from'] ?? null) {
                            $query->whereDate('posted_at', '>=', $data['from']);
                        }
                        if ($data['until'] ?? null) {
                            $query->whereDate('posted_at', '<=', $data['until']);
                        }
                    }),
            ])
            ->recordActions([
                ViewAction::make()->visible(fn () => Auth::user()?->can('journal_entries.view')),
                EditAction::make()
                    ->visible(fn ($record) => Auth::user()?->can('journal_entries.update') && $record->posted_at === null),
                DeleteAction::make()
                    ->visible(fn () => Auth::user()?->can('journal_entries.delete'))
                    ->visible(fn ($record) => $record->posted_at === null)
                    ->before(function ($record, DeleteAction $action) {
                        $pendingLines = $record->lines()->count();

                        if ($pendingLines > 0) {
                            Notification::make()
                                ->danger()
                                ->title('No se puede eliminar')
                                ->body("El asiento tiene {$pendingLines} línea(s) asociada(s).")
                                ->send();

                            $action->halt();
                        }
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Asiento eliminado')
                            ->body('El asiento contable se elimino correctamente.')
                    ),
            ])
            ->toolbarActions([
                ...CrudImportExportActions::make(
                    modelClass: JournalEntry::class,
                    module: 'journal_entries',
                    title: 'Asientos Contables',
                    filePrefix: 'asientos-contables',
                    fields: [
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
                    fieldLabels: [
                        'customer.name' => 'Cliente',
                        'journal_type' => 'Tipo de Asiento',
                        'entry_category' => 'Categoria',
                        'description' => 'Descripcion',
                        'reference' => 'Referencia',
                        'total_debit' => 'Debitos',
                        'total_credit' => 'Creditos',
                        'posted_at' => 'Posteado',
                        'posted_by' => 'Posteado Por',
                        'is_reversal' => 'Es Reverso',
                        'reversed_entry_id' => 'Asiento Revertido',
                        'source_type' => 'Tipo de Origen',
                        'source_id' => 'Origen',
                    ],
                    exportFields: [
                        'customer.name',
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
                ),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->can('journal_entries.delete'))
                        ->before(function ($records, DeleteBulkAction $action) {
                            $blockedReasons = [];

                            foreach ($records as $entry) {
                                if ($entry->posted_at !== null) {
                                    $blockedReasons[] = "Asiento #{$entry->id}: asiento publicado";
                                } else {
                                    $pendingLines = $entry->lines()->count();
                                    if ($pendingLines > 0) {
                                        $blockedReasons[] = "Asiento #{$entry->id}: {$pendingLines} línea(s)";
                                    }
                                }
                            }

                            if ($blockedReasons !== []) {
                                Notification::make()
                                    ->danger()
                                    ->title('No se puede eliminar')
                                    ->body("No se pueden eliminar asientos:\n\n- " . implode("\n- ", $blockedReasons))
                                    ->send();
                                $action->halt();
                            }
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Asientos eliminados')
                                ->body('Los asientos seleccionados se eliminaron correctamente.')
                        ),
                ]),
            ]);
    }
}

