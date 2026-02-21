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
        'classification', 
        'report_section',
        'normal_balance',
        'parent_id',
        'level',
        'status',
    ];

    // ✅ NUEVO: Definir constantes para clasificaciones
    public const CLASSIFICATIONS = [
        'activo_corriente' => 'Activo Corriente',
        'activo_no_corriente' => 'Activo No Corriente',
        'pasivo_corriente' => 'Pasivo Corriente',
        'pasivo_no_corriente' => 'Pasivo No Corriente',
        'patrimonio' => 'Patrimonio',
        'ingreso' => 'Ingreso',
        'gasto' => 'Gasto',
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
        
        if ($this->normal_balance === 'debit') {
            return $debe - $haber;
        }
        
        return $haber - $debe;
    }

    public function parent()
    {
        return $this->belongsTo(AccountingAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(AccountingAccount::class, 'parent_id');
    }

    // ✅ NUEVO: Scope para filtrar por clasificación
    public function scopeByClassification($query, $classification)
    {
        return $query->where('classification', $classification);
    }

    // ✅ NUEVO: Scope para activos
    public function scopeActivos($query)
    {
        return $query->where('type', 'Activo');
    }

    // ✅ NUEVO: Scope para pasivos
    public function scopePasivos($query)
    {
        return $query->where('type', 'Pasivo');
    }

    // ✅ NUEVO: Scope para patrimonio
    public function scopePatrimonio($query)
    {
        return $query->where('type', 'Patrimonio');
    }

    // ✅ NUEVO: Scope para ingresos
    public function scopeIngresos($query)
    {
        return $query->where('type', 'Ingreso');
    }

    // ✅ NUEVO: Scope para gastos
    public function scopeGastos($query)
    {
        return $query->where('type', 'Gasto');
    }
}