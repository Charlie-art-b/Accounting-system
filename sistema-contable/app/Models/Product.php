<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Validation\ValidationException;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'supplier_id',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $product): void {
            // Verificar si el producto está en algún inventario
            $inventoryCount = $product->inventoryProduct()->count();

            if ($inventoryCount > 0) {
                throw ValidationException::withMessages([
                    'name' => 'No se puede eliminar el producto. Está registrado en el inventario. Elimínalo del inventario primero.',
                ]);
            }
        });
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inventoryProduct()
    {
        return $this->hasOne(InventoryProduct::class);
    }
}
