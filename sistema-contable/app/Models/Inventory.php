<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Validation\ValidationException;

class Inventory extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'customer_id',
        'name',
    ];

    protected static function booted()
    {
        static::deleting(function ($inventory) {
            // Verificar si tiene productos con existencias activas
            $productsWithStock = $inventory->inventoryProducts()
                ->get()
                ->filter(function ($product) {
                    $existence = $product->stock_initial + $product->entries - $product->exits;
                    return $existence > 0;
                });

            if ($productsWithStock->count() > 0) {
                throw ValidationException::withMessages([
                    'inventory' => "No se puede eliminar el inventario '{$inventory->name}' porque tiene {$productsWithStock->count()} producto(s) con existencias activas."
                ]);
            }

            // Verificar si tiene productos con movimientos registrados
            $productsWithMovements = $inventory->inventoryProducts()
                ->where(function ($query) {
                    $query->where('entries', '>', 0)
                          ->orWhere('exits', '>', 0);
                })
                ->count();

            if ($productsWithMovements > 0) {
                throw ValidationException::withMessages([
                    'inventory' => "No se puede eliminar el inventario '{$inventory->name}' porque tiene {$productsWithMovements} producto(s) con movimientos registrados (entradas o salidas)."
                ]);
            }
        });
    }

    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    
    public function inventoryProducts()
    {
        return $this->hasMany(InventoryProduct::class);
    }

    /**
     * Crear un inventario con productos iniciales usando transacción.
     */
    public static function createWithInitialProducts(int $customerId, string $name, array $initialProducts): self
    {
        try {
            return DB::transaction(function () use ($customerId, $name, $initialProducts) {
                $inventory = self::create([
                    'customer_id' => $customerId,
                    'name' => $name,
                ]);

                foreach ($initialProducts as $productData) {
                    InventoryProduct::create([
                        'inventory_id' => $inventory->id,
                        'product_id' => $productData['product_id'],
                        'stock_initial' => $productData['stock_initial'] ?? 0,
                        'entries' => 0,
                        'exits' => 0,
                    ]);
                }

                Log::info("Inventario creado: {$inventory->id} para cliente {$customerId} con " . count($initialProducts) . " productos iniciales");

                return $inventory;
            });
        } catch (\Exception $e) {
            Log::error('Error al crear inventario con productos iniciales: ' . $e->getMessage());
            throw $e;
        }
    }
}
