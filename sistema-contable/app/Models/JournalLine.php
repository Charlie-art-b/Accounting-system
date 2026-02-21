<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class JournalLine extends Model
{
    use HasFactory;

    protected $table = 'journal_lines';

    protected $fillable = [
        'journal_entry_id',
        'accounting_account_id',
        'description',
        'debit',
        'credit',
        'source_type',
        'source_id',
        'metadata',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountingAccount::class, 'accounting_account_id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $line) {

            $line->debit = max(0, (float) ($line->debit ?? 0));
            $line->credit = max(0, (float) ($line->credit ?? 0));

            // Validación coherente con CHECK de la BD
            if (
                ($line->debit > 0 && $line->credit > 0) ||
                ($line->debit <= 0 && $line->credit <= 0)
            ) {
                throw ValidationException::withMessages([
                    'line' => 'Una línea debe tener solo débito o solo crédito.',
                ]);
            }

            $entry = $line->journalEntry ?? $line->journalEntry()->first();
            $acct = $line->account ?? AccountingAccount::find($line->accounting_account_id);

            if (! $acct || $acct->status !== 'Activa') {
                throw ValidationException::withMessages([
                    'accounting_account_id' => 'La cuenta no existe o no está activa.',
                ]);
            }

            if ($entry && $acct->customer_id !== $entry->customer_id) {
                throw ValidationException::withMessages([
                    'accounting_account_id' => 'La cuenta no pertenece al cliente del asiento.',
                ]);
            }
        });
    }
}