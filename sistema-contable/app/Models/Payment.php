<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'payable_type',
        'payable_id',
        'amount',
        'paid_at',
        'note',
        'is_reversal',
        'reversed_payment_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'is_reversal' => 'boolean',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reversal(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(self::class, 'reversed_payment_id');
    }
}
