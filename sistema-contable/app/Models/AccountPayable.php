<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class AccountPayable extends Model
{
    protected $table = 'accounts_payable';

    protected $fillable = [
        'supplier_id',
        'document_number',
        'issue_date',
        'payment_terms',
        'payment_period',
        'due_date',
        'type',
        'total_amount',
        'paid_amount',
        'payment_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $accountPayable): void {
            if ($accountPayable->status !== 'voided') {
                throw ValidationException::withMessages([
                    'status' => 'Solo se pueden eliminar cuentas por pagar canceladas (voided).',
                ]);
            }
        });

        static::saving(function (self $accountPayable): void {
            if (
                $accountPayable->exists
                && $accountPayable->getOriginal('status') === 'paid'
                && $accountPayable->isDirty()
            ) {
                throw ValidationException::withMessages([
                    'status' => 'No se permiten ediciones ni pagos adicionales cuando la cuenta está pagada.',
                ]);
            }

            $total = (float) ($accountPayable->total_amount ?? 0);
            $paid = (float) ($accountPayable->paid_amount ?? 0);

            if ($total < 0) {
                $total = 0;
            }

            if ($paid < 0) {
                $paid = 0;
            }

            if ($paid > $total) {
                $paid = $total;
            }

            $accountPayable->total_amount = $total;
            $accountPayable->paid_amount = $paid;

            if ($accountPayable->status === 'voided') {
                $accountPayable->paid_amount = 0;
                $accountPayable->payment_date = null;
                return;
            }

            if ($paid <= 0) {
                $accountPayable->status = 'pending';
                $accountPayable->payment_date = null;
                return;
            }

            if ($paid >= $total && $total > 0) {
                $accountPayable->status = 'paid';
                if (! $accountPayable->payment_date) {
                    $accountPayable->payment_date = now()->toDateString();
                }
                return;
            }

            $accountPayable->status = 'partial';
            $accountPayable->payment_date = null;
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    // calculo del saldo pendiente
    public function getPendingAmountAttribute(): float
    {
        return (float) $this->total_amount - (float) $this->paid_amount;
    }
}
