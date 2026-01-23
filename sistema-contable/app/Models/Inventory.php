<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'customer_id',
        'name',
    ];

    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    
    public function inventoryProducts()
    {
        return $this->hasMany(InventoryProduct::class);
    }
}
