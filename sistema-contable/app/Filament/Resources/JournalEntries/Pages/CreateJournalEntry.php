<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use App\Services\LedgerService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateJournalEntry extends CreateRecord
{
    protected static string $resource = JournalEntryResource::class;

    public ?string $totalsText = null;

    public function mount(): void
    {
        parent::mount();
        $this->updateTotalsText();
    }

    public function updated($propertyName): void
    {
        parent::updated($propertyName);
        $this->updateTotalsText();
    }

    private function updateTotalsText(): void
    {
        $lines = $this->data['lines'] ?? [];

        $debit = 0.0;
        $credit = 0.0;

        foreach ($lines as $line) {
            $debit  += (float) ($line['debit'] ?? 0);
            $credit += (float) ($line['credit'] ?? 0);
        }

        $diff = $debit - $credit;

        $this->totalsText = sprintf(
            'Débitos: %.2f | Créditos: %.2f | Diferencia: %.2f %s',
            $debit,
            $credit,
            $diff,
            (round($diff, 2) === 0.0 ? '✅ Balanceado' : '❌ Desbalanceado')
        );
    }

    private function isNotBalanced(): bool
    {
        $lines = $this->data['lines'] ?? [];

        $debit = 0.0;
        $credit = 0.0;

        foreach ($lines as $line) {
            $debit  += (float) ($line['debit'] ?? 0);
            $credit += (float) ($line['credit'] ?? 0);
        }

        return round($debit, 2) !== round($credit, 2);
    }

    protected function getFormActions(): array
    {
        return [
            // Guardar borrador (create normal)
            $this->getCreateFormAction()
                ->label('Guardar borrador'),

            // Postear (solo si balancea)
            Actions\Action::make('post')
                ->label('Postear')
                ->color('success')
                ->requiresConfirmation()
                ->disabled(fn () => $this->isNotBalanced())
                ->action(function (LedgerService $ledger) {
                    // crear registro draft
                    $record = $this->createRecord();

                    // postear con servicio
                    $ledger->postJournalEntry($record->fresh(['lines']), auth()->user());

                    Notification::make()
                        ->title('Asiento posteado')
                        ->success()
                        ->send();

                    $this->redirect(JournalEntryResource::getUrl('view', ['record' => $record]));
                }),
        ];
    }
}
