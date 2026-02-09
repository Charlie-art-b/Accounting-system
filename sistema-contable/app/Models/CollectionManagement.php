<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class CollectionManagement extends Model
{
    protected $fillable = [
        'account_receivable_id',
        'customer_id',
        'next_reminder_at',
        'reminder_attempts',
        'last_action',
        'notes',
    ];

    protected $casts = [
        'next_reminder_at' => 'datetime',
    ];

    protected $table = 'collection_managements';
    
    public function accountReceivable(): BelongsTo
    {
        return $this->belongsTo(AccountReceivable::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    
    // DERIVADOS
    // días de retraso
    public function getDaysLateAttribute(): int
    {
        $dueDate = $this->accountReceivable?->due_date;

        if (!$dueDate) return 0;

        $due = Carbon::parse($dueDate)->startOfDay();
        $today = now()->startOfDay();

        return $today->gt($due)
            ? $due->diffInDays($today)
            : 0;
    }

    // monto pendiente
    public function getPendingAmountAttribute(): float
    {
        $ar = $this->accountReceivable;

        if (!$ar) return 0;

        return (float) $ar->total_amount - (float) $ar->paid_amount;
    }

    // estado automatico
    public function getStatusAttribute(): string
    {
        $dueDate = $this->accountReceivable?->due_date;

        if (!$dueDate) return 'pending';

        $due = Carbon::parse($dueDate)->startOfDay();
        $today = now()->startOfDay();

        if ($today->gt($due)) {
            return 'overdue';
        }

        if ($today->diffInDays($due) <= 3) {
            return 'due_soon';
        }

        return 'pending'; 
    }
}
