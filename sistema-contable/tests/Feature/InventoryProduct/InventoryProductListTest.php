<?php

namespace Tests\Feature\InventoryProduct;

use Tests\TestCase;
use App\Models\InventoryProduct;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;

class InventoryProductListTest extends TestCase
{
    use RefreshDatabase;

    private function makeInventoryId(string $name): int
    {
        $customer = Customer::factory()->create();

        return (int) DB::table('inventories')->insertGetId([
            'customer_id' => $customer->id,
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeProductId(string $name): int
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
    public function list_inventory_products_by_inventory_id(): void
    {
        $inventoryA = $this->makeInventoryId('Inv A');
        $inventoryB = $this->makeInventoryId('Inv B');

        $product1 = $this->makeProductId('Prod 1');
        $product2 = $this->makeProductId('Prod 2');

        InventoryProduct::create(['inventory_id' => $inventoryA, 'product_id' => $product1]);
        InventoryProduct::create(['inventory_id' => $inventoryA, 'product_id' => $product2]);
        InventoryProduct::create(['inventory_id' => $inventoryB, 'product_id' => $product1]);

        $listA = InventoryProduct::query()->where('inventory_id', $inventoryA)->get();
        $this->assertCount(2, $listA);

        $listB = InventoryProduct::query()->where('inventory_id', $inventoryB)->get();
        $this->assertCount(1, $listB);
    }

    #[Test]
    public function can_find_inventory_product_by_inventory_and_product(): void
    {
        $inventoryId = $this->makeInventoryId('Inv Find');
        $productId = $this->makeProductId('Prod Find');

        InventoryProduct::create([
            'inventory_id' => $inventoryId,
            'product_id' => $productId,
            'stock_initial' => 2,
            'entries' => 1,
            'exits' => 0,
        ]);

        $found = InventoryProduct::query()
            ->where('inventory_id', $inventoryId)
            ->where('product_id', $productId)
            ->first();

        $this->assertNotNull($found);
        $this->assertSame(3, $found->existence);
    }
}
