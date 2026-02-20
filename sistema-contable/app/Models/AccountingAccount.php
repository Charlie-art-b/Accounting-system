<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingAccount extends Model
{
    protected $fillable = [
        'customer_id', 
        'code',
        'name',
        'type',
        'normal_balance',
        'status',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLine::class, 'accounting_account_id');
    }

    public function getDisplayAttribute(): string
    {
        return "{$this->code} - {$this->name}";
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Activa');
    }

    public function getSaldo(): float
    {
        $debe = $this->journalLines()->sum('debit');
        $haber = $this->journalLines()->sum('credit');
        
        return $this->normal_balance === 'debit' 
            ? $debe - $haber 
            : $haber - $debe;
    }
}
