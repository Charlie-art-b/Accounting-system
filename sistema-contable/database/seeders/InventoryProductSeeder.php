<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\InventoryProduct;

class InventoryProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inventoryProducts = [
            
            [
                'inventory_id' => 1,
                'product_id' => 1,
                'stock_initial' => 10,
                'entries' => 5,
                'exits' => 2,
            ],
            [
                'inventory_id' => 1,
                'product_id' => 2,
                'stock_initial' => 8,
                'entries' => 3,
                'exits' => 1,
            ],
            [
                'inventory_id' => 1,
                'product_id' => 3,
                'stock_initial' => 15,
                'entries' => 0,
                'exits' => 5,
            ],
            // Inventario 2 (Cliente 1)
            [
                'inventory_id' => 2,
                'product_id' => 4,
                'stock_initial' => 20,
                'entries' => 10,
                'exits' => 8,
            ],
            [
                'inventory_id' => 2,
                'product_id' => 5,
                'stock_initial' => 5,
                'entries' => 2,
                'exits' => 0,
            ],
            // Inventario 3 (Cliente 2)
            [
                'inventory_id' => 3,
                'product_id' => 6,
                'stock_initial' => 12,
                'entries' => 7,
                'exits' => 4,
            ],
            [
                'inventory_id' => 3,
                'product_id' => 7,
                'stock_initial' => 6,
                'entries' => 1,
                'exits' => 1,
            ],
            [
                'inventory_id' => 3,
                'product_id' => 8,
                'stock_initial' => 9,
                'entries' => 4,
                'exits' => 3,
            ],
            // Inventario 4 (Cliente 3)
            [
                'inventory_id' => 4,
                'product_id' => 9,
                'stock_initial' => 25,
                'entries' => 15,
                'exits' => 10,
            ],
            [
                'inventory_id' => 4,
                'product_id' => 10,
                'stock_initial' => 3,
                'entries' => 0,
                'exits' => 1,
            ],
        ];

        foreach ($inventoryProducts as $inventoryProduct) {
            InventoryProduct::updateOrCreate(
                ['inventory_id' => $inventoryProduct['inventory_id'], 'product_id' => $inventoryProduct['product_id']],
                $inventoryProduct
            );
        }
    }
}
