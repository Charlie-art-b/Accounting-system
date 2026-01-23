<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'supplier_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inventoryProduct()
    {
        return $this->hasOne(InventoryProduct::class);
    }
}
