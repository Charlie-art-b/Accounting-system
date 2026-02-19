<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingAccount extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'status',
    ];
    public function customer(){

        return $this->belongsTo(Customer::class);
    }

    //para Contabilidad General
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

}
