<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryProduct extends Model
{
    protected $fillable = [
        'inventory_id',
        'product_id',
        'stock_initial',
        'entries',
        'exits',
    ];

    /**
     * El producto pertenece a un inventario
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getExistenceAttribute(): int
    {
        return $this->stock_initial + $this->entries - $this->exits;
    }
}
