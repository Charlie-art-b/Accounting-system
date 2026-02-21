<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Validation\ValidationException;
use App\Models\Supplier;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'first_last_name',
        'second_last_name',
        'id_type',
        'identification',
        'email',
        'phone',
        'address',
        'customer_type',
        'status',
        'notes',
    ];

    protected $casts = [
        'customer_type' => 'string',
        'status' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $customer): void {
            // Verificar si tiene cuentas por cobrar con saldo pendiente
            $accountsWithPendingBalance = $customer->accountReceivables()
                ->whereIn('status', ['pending', 'partial'])
                ->count();

            if ($accountsWithPendingBalance > 0) {
                throw ValidationException::withMessages([
                    'name' => "No se puede eliminar el cliente. Tiene {$accountsWithPendingBalance} cuenta(s) por cobrar con saldo pendiente.",
                ]);
            }
        });
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => strtolower(trim($value))
        );
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('customer_type', $type);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'customer_supplier')
                    ->withTimestamps();
    }

    public function accountingAccounts()
    {
        return $this->hasMany(AccountingAccount::class);
    }

    public function accountReceivables()
    {
        return $this->hasMany(AccountReceivable::class);
    }
}