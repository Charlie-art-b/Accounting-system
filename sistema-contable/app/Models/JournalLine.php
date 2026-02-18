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
        'chart_of_account_id',
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
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $line) {
            $line->debit = max(0, (float) ($line->debit ?? 0));
            $line->credit = max(0, (float) ($line->credit ?? 0));

            if ($line->debit > 0 && $line->credit > 0) {
                throw ValidationException::withMessages([
                    'line' => 'Una línea no puede tener débito y crédito al mismo tiempo.',
                ]);
            }

            if ($line->debit <= 0 && $line->credit <= 0) {
                throw ValidationException::withMessages([
                    'line' => 'Una línea debe tener débito o crédito mayor que cero.',
                ]);
            }

            // Ensure account exists and is active
            if ($line->chart_of_account_id) {
                $acct = ChartOfAccount::find($line->chart_of_account_id);
                if (! $acct || ! $acct->is_active) {
                    throw ValidationException::withMessages([
                        'chart_of_account_id' => 'La cuenta contable no existe o no está activa.',
                    ]);
                }
            }
        });
    }
}
