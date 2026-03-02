<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use App\Services\LedgerService;
use Filament\Actions\Action;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Filament\Facades\Filament;
use Filament\Actions\ViewAction;

class EditJournalEntry extends EditRecord
{
    protected static string $resource = JournalEntryResource::class;

    public ?string $totalsText = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Volver a la lista')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),

            ViewAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
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
        $this->updateTotalsText();
    }

    private function getTotals(): array
    {
        $lines = $this->data['lines'] ?? [];

        $debit = 0.0;
        $credit = 0.0;

        foreach ($lines as $line) {
            $debit  += (float) ($line['debit'] ?? 0);
            $credit += (float) ($line['credit'] ?? 0);
        }

        return [
            'debit' => round($debit, 2),
            'credit' => round($credit, 2),
        ];
    }

    private function hasNoLines(): bool
    {
        $lines = $this->data['lines'] ?? [];
        return empty($lines);
    }

    private function isNotBalanced(): bool
    {
        $totals = $this->getTotals();
        return $totals['debit'] !== $totals['credit'];
    }

    private function isZeroTotals(): bool
    {
        $totals = $this->getTotals();
        return $totals['debit'] <= 0 || $totals['credit'] <= 0;
    }

    private function updateTotalsText(): void
    {
        $totals = $this->getTotals();

        $debit = $totals['debit'];
        $credit = $totals['credit'];
        $diff = round($debit - $credit, 2);

        $this->totalsText = sprintf(
            'Débitos: %.2f | Créditos: %.2f | Diferencia: %.2f %s',
            $debit,
            $credit,
            $diff,
            ($diff === 0.0 ? '✅ Balanceado' : '❌ Desbalanceado')
        );
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
                ->disabled(fn () => $this->hasNoLines() || $this->isNotBalanced() || $this->isZeroTotals())
                ->action(function (LedgerService $ledger) {
                    try {
                        // validar líneas antes de postear (con $this->data)
                        $this->validateLinesForPosting();

                        // guardar cambios primero
                        $this->save();

                        $record = $this->record->fresh(['lines']);

                        $user = Filament::auth()->user(); 
                        $ledger->postJournalEntry($record, $user);

                        Notification::make()
                            ->title('Asiento posteado')
                            ->success()
                            ->send();

                        $this->redirect(JournalEntryResource::getUrl('view', ['record' => $record]));
                    } catch (ValidationException $e) {
                        $msg = collect($e->errors())->flatten()->first() ?? 'No se pudo postear el asiento.';

                        Notification::make()
                            ->title('No se pudo postear')
                            ->body($msg)
                            ->danger()
                            ->send();

                        throw new Halt();
                    } catch (Halt $e) {
                        throw $e;
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Error inesperado')
                            ->body('Ocurrió un error al intentar postear. Revisa las líneas e inténtalo de nuevo.')
                            ->danger()
                            ->send();

                        throw new Halt();
                    }
                }),

            Action::make('cancel')
                ->label('Cancelar')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }

    private function validateLinesForPosting(): void
    {
        $lines = $this->data['lines'] ?? [];

        if (empty($lines)) {
            Notification::make()
                ->title('Revisa las líneas')
                ->body('Debes agregar al menos una línea para postear.')
                ->danger()
                ->send();

            throw new Halt();
        }

        foreach ($lines as $index => $line) {
            $debit  = (float) ($line['debit'] ?? 0);
            $credit = (float) ($line['credit'] ?? 0);

            if ($debit > 0 && $credit > 0) {
                $msg = 'Una línea no puede tener débito y crédito al mismo tiempo.';

                $this->addError("data.lines.$index.debit", $msg);
                $this->addError("data.lines.$index.credit", $msg);

                Notification::make()->title('Revisa las líneas')->body($msg)->danger()->send();
                throw new Halt();
            }

            if ($debit <= 0 && $credit <= 0) {
                $msg = 'Cada línea debe tener débito o crédito mayor que cero para postear.';

                $this->addError("data.lines.$index.debit", $msg);
                $this->addError("data.lines.$index.credit", $msg);

                Notification::make()->title('Revisa las líneas')->body($msg)->danger()->send();
                throw new Halt();
            }
        }

        if ($this->isZeroTotals()) {
            Notification::make()
                ->title('Totales inválidos')
                ->body('El asiento no puede postearse con totales en cero.')
                ->danger()
                ->send();

            throw new Halt();
        }

        if ($this->isNotBalanced()) {
            Notification::make()
                ->title('Asiento desbalanceado')
                ->body('El asiento debe estar balanceado para postear.')
                ->danger()
                ->send();

            throw new Halt();
        }
    }
}