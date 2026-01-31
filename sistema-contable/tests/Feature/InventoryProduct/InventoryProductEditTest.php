<?php

namespace Tests\Feature\InventoryProduct;

use Tests\TestCase;
use App\Models\InventoryProduct;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;

class InventoryProductEditTest extends TestCase
{
    use RefreshDatabase;

    private function makeInventoryId(): int
    {
        $customer = Customer::factory()->create();

        return (int) DB::table('inventories')->insertGetId([
            'customer_id' => $customer->id,
            'name' => 'Inventario Edit',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeProductId(string $name = 'Producto Edit'): int
    {
        return (int) DB::table('products')->insertGetId([
            'name' => $name,
            'description' => null,
            'supplier_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    #[Test]
    public function can_edit_stock_fields_and_existence_updates(): void
    {
        $inventoryId = $this->makeInventoryId();
        $productId = $this->makeProductId();

        $invProd = InventoryProduct::create([
            'inventory_id' => $inventoryId,
            'product_id' => $productId,
            'stock_initial' => 5,
            'entries' => 2,
            'exits' => 1,
        ]);

        $this->assertSame(6, $invProd->existence); // 5+2-1

        $invProd->update([
            'stock_initial' => 10,
            'entries' => 3,
            'exits' => 4,
        ]);

        $invProd->refresh();

        $this->assertDatabaseHas('inventory_products', [
            'id' => $invProd->id,
            'stock_initial' => 10,
            'entries' => 3,
            'exits' => 4,
        ]);

        $this->assertSame(9, $invProd->existence); // 10+3-4
    }

    #[Test]
    public function can_move_product_to_another_product_id_if_not_duplicate_pair(): void
    {
        $inventoryId = $this->makeInventoryId();

        $productId1 = $this->makeProductId('Producto 1');
        $productId2 = $this->makeProductId('Producto 2');

        $invProd = InventoryProduct::create([
            'inventory_id' => $inventoryId,
            'product_id' => $productId1,
        ]);

        $invProd->update([
            'product_id' => $productId2,
        ]);

        $this->assertDatabaseHas('inventory_products', [
            'id' => $invProd->id,
            'inventory_id' => $inventoryId,
            'product_id' => $productId2,
        ]);
    }
}
