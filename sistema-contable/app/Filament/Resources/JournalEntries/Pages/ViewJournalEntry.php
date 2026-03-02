<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use App\Services\LedgerService;
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

            EditAction::make()
                ->visible(fn () => $this->record->posted_at === null),

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
