<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
}