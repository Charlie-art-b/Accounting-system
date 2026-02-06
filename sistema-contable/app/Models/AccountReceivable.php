<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AccountReceivable extends Model
{
    protected $table = 'accounts_receivable';

    protected $fillable = [
        'customer_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'description',
        'total_amount',
        'paid_amount',
        'status',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    protected static function booted(): void 
    {
        static::creating(function (self $model) {
            $model->paid_amount = 0;
            $model->status = 'pending';
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function collectionManagement(): HasOne
    {
        return $this->hasOne(CollectionManagement::class);
    }

    //calculo del saldo pendiente 
    public function getPendingAmountAttribute(): float
    {
        return (float) $this->total_amount - (float) $this->paid_amount;
    }
}
