<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    /**
     * Registrar una entrada de stock con manejo de errores y transacción.
     */
    public static function registerEntry(int $inventoryId, int $productId, int $quantity): bool
    {
        try {
            return DB::transaction(function () use ($inventoryId, $productId, $quantity) {
                $inventoryProduct = self::where('inventory_id', $inventoryId)
                                        ->where('product_id', $productId)
                                        ->first();

                if (!$inventoryProduct) {
                    throw new \Exception('Producto no encontrado en el inventario.');
                }

                $inventoryProduct->increment('entries', $quantity);

                Log::info("Entrada registrada: Inventario {$inventoryId}, Producto {$productId}, Cantidad {$quantity}");

                return true;
            });
        } catch (\Exception $e) {
            Log::error('Error al registrar entrada de stock: ' . $e->getMessage());
            throw $e; // Re-lanzar para que el Handler lo maneje
        }
    }

    /**
     * Registrar una salida de stock con manejo de errores y transacción.
     */
    public static function registerExit(int $inventoryId, int $productId, int $quantity): bool
    {
        try {
            return DB::transaction(function () use ($inventoryId, $productId, $quantity) {
                $inventoryProduct = self::where('inventory_id', $inventoryId)
                                        ->where('product_id', $productId)
                                        ->first();

                if (!$inventoryProduct) {
                    throw new \Exception('Producto no encontrado en el inventario.');
                }

                if ($inventoryProduct->existence < $quantity) {
                    throw new \Exception('Stock insuficiente para la salida.');
                }

                $inventoryProduct->increment('exits', $quantity);

                Log::info("Salida registrada: Inventario {$inventoryId}, Producto {$productId}, Cantidad {$quantity}");

                return true;
            });
        } catch (\Exception $e) {
            Log::error('Error al registrar salida de stock: ' . $e->getMessage());
            throw $e;
        }
    }
}
