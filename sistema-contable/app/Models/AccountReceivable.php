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
            $model->paid_amount ??= 0;
            $model->status ??= 'pending';
        });

        static::saving(function (self $model) {
            $model->total_amount = max(0, (float) $model->total_amount);
            $model->paid_amount  = max(0, (float) $model->paid_amount);

            // evitar pagado > total
            if ($model->paid_amount > $model->total_amount) {
                $model->paid_amount = $model->total_amount;
            }

            // estado automático
            if ($model->total_amount > 0 && $model->paid_amount >= $model->total_amount) {
                $model->status = 'paid';
            } elseif ($model->paid_amount > 0) {
                $model->status = 'partial';
            } else {
                $model->status = 'pending';
            }
        });

        // evitar eliminacion si esta pendiente o parcial
        static::deleting(function (self $model) {
            if (in_array($model->status, ['pending', 'partial'], true)) {
                throw new \Exception(
                    'No se puede eliminar una cuenta por cobrar en estado Pendiente o Parcial.'
                );
            }
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
        return max(0, (float) $this->total_amount - (float) $this->paid_amount);
    }
}
