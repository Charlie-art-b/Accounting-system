<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory;
    
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
