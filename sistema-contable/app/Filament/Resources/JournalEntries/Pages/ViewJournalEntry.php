<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use App\Services\LedgerService;
use App\Services\PdfFallbackService;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Illuminate\Validation\ValidationException;

class ViewJournalEntry extends ViewRecord
{
    protected static string $resource = JournalEntryResource::class;

    public function getTitle(): string
    {
        return 'Detalle del Asiento #' . $this->record->id;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Volver a la lista')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),

            Action::make('export_pdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('warning')
                ->visible(fn () => auth()->user()?->can('journal_entries.view'))
                ->action(fn () => app(PdfFallbackService::class)->download(
                    view: 'exports.generic-model-pdf',
                    data: [
                        'title' => 'Asiento Contable',
                        'fields' => ['customer.name', 'journal_type', 'entry_category', 'description', 'reference', 'total_debit', 'total_credit', 'posted_at', 'posted_by', 'is_reversal', 'reversed_entry_id', 'source_type', 'source_id'],
                        'displayFields' => ['Cliente', 'Tipo de Asiento', 'Categoría', 'Descripción', 'Referencia', 'Débitos', 'Créditos', 'Posteado', 'Posteado Por', 'Es Reverso', 'Asiento Revertido', 'Tipo de Origen', 'Origen'],
                        'records' => collect([$this->record]),
                    ],
                    baseFileName: 'asiento_contable_' . $this->record->id . '_' . now()->format('Y-m-d_H-i-s'),
                    paper: 'a4',
                    orientation: 'landscape',
                )),

            EditAction::make()
                ->visible(fn () => auth()->user()?->can('journal_entries.update') && $this->record->posted_at === null),

            Action::make('reverse')
                ->label('Revertir')
                ->color('warning')
                ->icon('heroicon-o-arrow-uturn-left')
                ->visible(fn () => $this->record->posted_at !== null)
                ->requiresConfirmation()
                ->form([
                    Textarea::make('memo')
                        ->label('Motivo / Memo (opcional)')
                        ->rows(3),
                ])
                ->action(function (array $data, LedgerService $ledger) {
                    $ledger->reverseJournalEntry(
                        $this->record->fresh(['lines']),
                        auth()->user(),
                        $data['memo'] ?? null,
                        true // autopost
                    );

                    Notification::make()
                        ->title('Asiento revertido')
                        ->success()
                        ->send();

                    $this->redirect(JournalEntryResource::getUrl('index'));
                }),
        ];
    }
}
