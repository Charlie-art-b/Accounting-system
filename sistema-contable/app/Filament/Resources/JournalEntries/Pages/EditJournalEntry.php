<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use App\Services\LedgerService;
use Filament\Actions\Action;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EditJournalEntry extends EditRecord
{
    protected static string $resource = JournalEntryResource::class;

    public ?string $totalsText = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->url($this->getResource()::getUrl('index'))
                ->tooltip('Volver a la lista'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // bloquear si está posteado
        if ($this->record->posted_at !== null) {
            throw new HttpException(403, 'No se puede editar un asiento posteado.');
        }

        return $data;
    }

    public function mount(string|int $record): void
    {
        parent::mount($record);

        $this->updateTotalsText();
    }

    public function updated($propertyName): void
    {
        //parent::updated($propertyName);
        $this->updateTotalsText();
    }

    private function updateTotalsText(): void
    {
        $state = $this->form->getState();
        $lines = $state['lines'] ?? [];

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
        $state = $this->form->getState();
        $lines = $state['lines'] ?? [];

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
            $this->getSaveFormAction()
                ->label('Guardar cambios'),

            Actions\Action::make('post')
                ->label('Postear')
                ->color('success')
                ->requiresConfirmation()
                ->disabled(fn () => $this->isNotBalanced())
                ->action(function (LedgerService $ledger) {
                    // guardar cambios primero
                    $this->save();

                    $record = $this->record->fresh(['lines']);

                    // por el momento sin usuarios
                    $ledger->postJournalEntry($record, null);

                    Notification::make()
                        ->title('Asiento posteado')
                        ->success()
                        ->send();

                    $this->redirect(JournalEntryResource::getUrl('view', ['record' => $record]));
                }),
        ];
    }
}