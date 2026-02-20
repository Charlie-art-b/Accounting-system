<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use App\Services\LedgerService;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Validation\ValidationException;

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

    protected function getFormActions(): array
    {
        return [
            // Guardar borrador (create normal)
            $this->getCreateFormAction()
                ->label('Guardar borrador'),

            // Postear (solo si cumple reglas)
            Actions\Action::make('post')
                ->label('Postear')
                ->color('success')
                ->requiresConfirmation()
                ->disabled(fn () => $this->hasNoLines() || $this->isNotBalanced() || $this->isZeroTotals())
                ->action(function (LedgerService $ledger) {
                    try {
                        // Validaciones UI antes de crear/postear
                        $this->validateLinesForPosting();

                        // Obtener data del form y crear el asiento
                        $data = $this->form->getState();
                        $record = $this->handleRecordCreation($data);

                        // Guardar relaciones (Repeater -> relationship() = lines)
                        $this->form->model($record)->saveRelationships();

                        // Postear con el servicio (validación final en backend)
                        $ledger->postJournalEntry($record->fresh(['lines']), auth()->user());

                        Notification::make()
                            ->title('Asiento posteado')
                            ->success()
                            ->send();

                        $this->redirect(JournalEntryResource::getUrl('view', ['record' => $record]));
                    } catch (ValidationException $e) {
                        // Mensaje claro (toma el primer error)
                        $msg = collect($e->errors())->flatten()->first() ?? 'No se pudo postear el asiento.';

                        Notification::make()
                            ->title('No se pudo postear')
                            ->body($msg)
                            ->danger()
                            ->send();

                        // Importante: detener la acción sin crashear la página
                        throw new Halt();
                    } catch (Halt $e) {
                        // Ya se notifico antes, solo detenemos.
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
        //$state = $this->form->getState();
        //$lines = $state['lines'] ?? [];
        $lines = $this->data['lines'] ?? [];

        if (empty($lines)) {
            $msg = 'Debes agregar al menos una línea para postear.';
            Notification::make()->title('Revisa las líneas')->body($msg)->danger()->send();
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

        // Total > 0 y balanceado
        if ($this->isZeroTotals()) {
            $msg = 'El asiento no puede postearse con totales en cero.';
            Notification::make()->title('Totales inválidos')->body($msg)->danger()->send();
            throw new Halt();
        }

        if ($this->isNotBalanced()) {
            $msg = 'El asiento debe estar balanceado para postear (Débitos = Créditos).';
            Notification::make()->title('Asiento desbalanceado')->body($msg)->danger()->send();
            throw new Halt();
        }
    }
}