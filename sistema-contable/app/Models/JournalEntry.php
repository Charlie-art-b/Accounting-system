<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JournalEntry extends Model
{
    use HasFactory;

    protected $table = 'journal_entries';

    protected $fillable = [
        'journal_type',
        'description',
        'reference',
        'total_debit',
        'total_credit',
        'fiscal_period_id',
        'posted_at',
        'posted_by',
        'is_reversal',
        'reversed_entry_id',
        'source_type',
        'source_id',
        'metadata',
    ];

    protected $casts = [
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'posted_at' => 'datetime',
        'metadata' => 'array',
        'is_reversal' => 'boolean',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class, 'journal_entry_id');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function reversedEntry(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversed_entry_id');
    }

    public function reversal(): HasMany
    {
        return $this->hasMany(self::class, 'reversed_entry_id');
    }

    public function isBalanced(): bool
    {
        $debits = $this->lines->sum(fn ($l) => (float) $l->debit);
        $credits = $this->lines->sum(fn ($l) => (float) $l->credit);

        return bccomp((string) $debits, (string) $credits, 2) === 0;
    }

    public function calculateTotals(): void
    {
        $this->total_debit = $this->lines->sum(fn ($l) => (float) $l->debit);
        $this->total_credit = $this->lines->sum(fn ($l) => (float) $l->credit);
    }

    protected static function booted(): void
    {
        static::saving(function (self $entry) {
            // Ensure totals are non-negative
            $entry->total_debit = max(0, (float) ($entry->total_debit ?? 0));
            $entry->total_credit = max(0, (float) ($entry->total_credit ?? 0));

            // If lines are loaded, ensure totals match lines
            if ($entry->relationLoaded('lines')) {
                $debits = $entry->lines->sum(fn ($l) => (float) $l->debit);
                $credits = $entry->lines->sum(fn ($l) => (float) $l->credit);

                if (bccomp((string) $debits, (string) $credits, 2) !== 0) {
                    throw ValidationException::withMessages([
                        'lines' => 'Las líneas del asiento no están balanceadas (debits != credits).',
                    ]);
                }

                $entry->total_debit = $debits;
                $entry->total_credit = $credits;
            }
        });
    }

    /**
     * Basic post operation skeleton. Real posting (balances, events)
     * should be implemented in a LedgerService and use DB transactions.
     */
    public function post($user = null): void
    {
        if (! $this->relationLoaded('lines')) {
            $this->load('lines');
        }

        if (! $this->isBalanced()) {
            throw ValidationException::withMessages(['entry' => 'El asiento no está balanceado.']);
        }

        DB::transaction(function () use ($user) {
            $this->posted_at = now();
            if ($user && method_exists($user, 'getKey')) {
                $this->posted_by = $user->getKey();
            }
            $this->save();
            // Actual update de balances y registros auxiliares debe hacerse en LedgerService.
        });
    }
}
