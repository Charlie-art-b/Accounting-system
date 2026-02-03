<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

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
