<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inventory;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inventories = [
            [
                'customer_id' => 1,
                'name' => 'Inventario Principal Cliente 1',
            ],
            [
                'customer_id' => 1,
                'name' => 'Inventario Secundario Cliente 1',
            ],
            [
                'customer_id' => 2,
                'name' => 'Inventario Principal Cliente 2',
            ],
            [
                'customer_id' => 3,
                'name' => 'Inventario Empresa Cliente 3',
            ],
        ];

        foreach ($inventories as $inventory) {
            Inventory::updateOrCreate(
                ['name' => $inventory['name'], 'customer_id' => $inventory['customer_id']],
                $inventory
            );
        }
    }
}
