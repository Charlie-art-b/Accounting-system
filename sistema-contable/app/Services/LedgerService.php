<?php

namespace App\Services;

use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LedgerService
{
    public function postJournalEntry(JournalEntry $entry, ?User $user = null): JournalEntry
    {
        if ($entry->posted_at !== null) {
            throw ValidationException::withMessages([
                'posted_at' => 'El asiento ya está posteado.',
            ]);
        }

        $entry->loadMissing('lines');

         if ($entry->lines->isEmpty()) {
            throw ValidationException::withMessages([
                'lines' => 'El asiento no tiene líneas.',
            ]);
        }

        $debit = 0.0;
        $credit = 0.0;

        foreach ($entry->lines as $line) {
            $debit += (float) $line->debit;
            $credit += (float) $line->credit;
        }

        $debit = round($debit, 2);
        $credit = round($credit, 2);

         //No permitir asiento en cero
        if ($debit <= 0 || $credit <= 0) {
            throw ValidationException::withMessages([
                'lines' => 'El asiento debe tener montos mayores a cero.',
            ]);
        }

        //No permitir desbalance
        if ($debit !== $credit) {
            throw ValidationException::withMessages([
                'lines' => 'El asiento no está balanceado.',
            ]);
        }

        /*if (round($debit, 2) !== round($credit, 2)) {
            throw ValidationException::withMessages([
                'lines' => 'El asiento no está balanceado.',
            ]);
        }*/

        return DB::transaction(function () use ($entry, $user, $debit, $credit) {

            $entry->total_debit = $debit;
            $entry->total_credit = $credit;
            $entry->posted_at = now();
            $entry->posted_by = $user?->id;

            $entry->save();

            return $entry->fresh(['lines', 'postedBy']);
        });
    }

    public function reverseJournalEntry(
        JournalEntry $entry,
        User $user,
        ?string $memo = null,
        bool $autoPost = true
    ): JournalEntry {

        if ($entry->posted_at === null) {
            throw ValidationException::withMessages([
                'posted_at' => 'Solo se pueden revertir asientos posteados.',
            ]);
        }

        return DB::transaction(function () use ($entry, $user, $memo, $autoPost) {

            $entry->loadMissing('lines');

            $reversal = JournalEntry::create([
                'customer_id' => $entry->customer_id,
                'journal_type' => 'reversal',
                'description' => $memo ?: "Reverso del asiento #{$entry->id}",
                'reference' => $entry->reference
                    ? "REV-{$entry->reference}"
                    : "REV-{$entry->id}",
                'fiscal_period_id' => $entry->fiscal_period_id,
                'is_reversal' => true,
                'reversed_entry_id' => $entry->id,
            ]);

            foreach ($entry->lines as $line) {
                $reversal->lines()->create([
                    'accounting_account_id' => $line->accounting_account_id,
                    'description' => $line->description,
                    'debit' => $line->credit,
                    'credit' => $line->debit,
                ]);
            }

            if ($autoPost) {
                $this->postJournalEntry($reversal->fresh(['lines']), $user);
            }

            return $reversal->fresh(['lines', 'postedBy']);
        });
    }
}
