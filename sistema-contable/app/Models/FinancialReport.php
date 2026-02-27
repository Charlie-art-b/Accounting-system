<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialReport extends Model
{
    protected $fillable = [
        'customer_id',
        'report_type',
        'fecha_inicio',
        'fecha_fin',
        'tasa_impuestos',
        'payload',
        'generated_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'generated_at' => 'datetime',
        'tasa_impuestos' => 'decimal:4',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
