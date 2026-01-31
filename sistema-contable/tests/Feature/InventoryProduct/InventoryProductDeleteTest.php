<?php

namespace Tests\Feature\InventoryProduct;

use Tests\TestCase;
use App\Models\InventoryProduct;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;

class InventoryProductDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function makeInventoryId(): int
    {
        $customer = Customer::factory()->create();

        return (int) DB::table('inventories')->insertGetId([
            'customer_id' => $customer->id,
            'name' => 'Inventario Delete',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeProductId(): int
    {
        return (int) DB::table('products')->insertGetId([
            'name' => 'Producto Delete',
            'description' => null,
            'supplier_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    #[Test]
    public function can_delete_inventory_product_record(): void
    {
        $inventoryId = $this->makeInventoryId();
        $productId = $this->makeProductId();

        $invProd = InventoryProduct::create([
            'inventory_id' => $inventoryId,
            'product_id' => $productId,
        ]);

        $invProd->delete();

        $this->assertDatabaseMissing('inventory_products', [
            'id' => $invProd->id,
        ]);
    }

    #[Test]
    public function deleting_inventory_cascades_inventory_products(): void
    {
        $inventoryId = $this->makeInventoryId();
        $productId = $this->makeProductId();

        $invProd = InventoryProduct::create([
            'inventory_id' => $inventoryId,
            'product_id' => $productId,
        ]);

        DB::table('inventories')->where('id', $inventoryId)->delete();

        $this->assertDatabaseMissing('inventory_products', [
            'id' => $invProd->id,
        ]);
    }

    #[Test]
    public function deleting_product_cascades_inventory_products(): void
    {
        $inventoryId = $this->makeInventoryId();
        $productId = $this->makeProductId();

        $invProd = InventoryProduct::create([
            'inventory_id' => $inventoryId,
            'product_id' => $productId,
        ]);

        DB::table('products')->where('id', $productId)->delete();

        $this->assertDatabaseMissing('inventory_products', [
            'id' => $invProd->id,
        ]);
    }
}
