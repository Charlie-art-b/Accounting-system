<?php

namespace App\Filament\Resources\JournalEntries\Tables;

use App\Filament\Support\CrudImportExportActions;
use App\Models\JournalEntry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Auth;

class JournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'asc')
            ->columns([
                TextColumn::make('id')->label('Nº de asiento')->sortable(),
                TextColumn::make('customer.name')->label('Cliente')->searchable()->sortable(),
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
                TextColumn::make('reference')->label('Referencia')->searchable(),
                TextColumn::make('description')->label('Descripción')->limit(40)->searchable(),
                TextColumn::make('total_debit')->label('Débitos')->numeric(decimalPlaces: 2)->sortable()->money('CRC'),
                TextColumn::make('total_credit')->label('Créditos')->numeric(decimalPlaces: 2)->sortable()->money('CRC'),
                TextColumn::make('posted_at')->label('Posteado')->dateTime()->sortable()->placeholder('BORRADOR'),
                TextColumn::make('postedBy.name')->label('Posteado por')->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('customer_id')->label('Cliente')->relationship('customer', 'name')->preload(),
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
                EditAction::make()->visible(fn () => Auth::user()?->can('journal_entries.update'))
                    ->visible(fn ($record) => $record->posted_at === null),
                DeleteAction::make()->visible(fn () => Auth::user()?->can('journal_entries.delete'))
                    ->visible(fn ($record) => $record->posted_at === null)
                    ->before(function ($record, DeleteAction $action) {
                        $pendingDetails = $record->details()->count();
                        if ($pendingDetails > 0) {
                            Notification::make()
                                ->danger()
                                ->title('NO SE PUEDE ELIMINAR')
                                ->body("El asiento contable tiene {$pendingDetails} detalle(s) asociado(s).")
                                ->send();
                            $action->halt();
                        }
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('¡Asiento eliminado!')
                            ->body('El asiento contable ha sido eliminado correctamente.')
                    ),
            ])
            ->toolbarActions([
                ...CrudImportExportActions::make(
                    modelClass: JournalEntry::class,
                    module: 'journal_entries',
                    title: 'Asientos Contables',
                    filePrefix: 'asientos-contables',
                    fields: [
                        'customer_id','journal_type','entry_category','description','reference',
                        'total_debit','total_credit','posted_at','posted_by','is_reversal',
                        'reversed_entry_id','source_type','source_id',
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
                        'entry_category' => 'Categoría',
                        'description' => 'Descripción',
                        'reference' => 'Referencia',
                        'total_debit' => 'Débitos',
                        'total_credit' => 'Créditos',
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
                        ->before(function ($records, DeleteBulkAction $action) {
                            $blockedCount = 0;
                            $blockedReasons = [];
                            foreach ($records as $entry) {
                                $pendingDetails = $entry->details()->count();
                                if ($pendingDetails > 0) {
                                    $blockedCount++;
                                    $blockedReasons[] = "Asiento #{$entry->id}: {$pendingDetails} detalle(s) pendiente(s)";
                                }
                            }
                            if ($blockedCount > 0) {
                                Notification::make()
                                    ->danger()
                                    ->title('NO SE PUEDE ELIMINAR')
                                    ->body("No se pueden eliminar {$blockedCount} asiento(s):\n\n• " . implode("\n• ", $blockedReasons))
                                    ->send();
                                $action->halt();
                            }
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('¡Asientos eliminados!')
                                ->body('Los asientos seleccionados han sido eliminados correctamente.')
                        ),
                ]),
            ]);
    }
}