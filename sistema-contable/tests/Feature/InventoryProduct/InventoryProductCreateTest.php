<?php

namespace Tests\Feature\InventoryProduct;

use Tests\TestCase;
use App\Models\InventoryProduct;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;

class InventoryProductCreateTest extends TestCase
{
    use RefreshDatabase;

    private function makeInventoryId(): int
    {
        $customer = Customer::factory()->create();

        return (int) DB::table('inventories')->insertGetId([
            'customer_id' => $customer->id,
            'name' => 'Inventario QA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeProductId(): int
    {
        return (int) DB::table('products')->insertGetId([
            'name' => 'Producto QA',
            'description' => null,
            'supplier_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    #[Test]
    public function create_inventory_product_with_defaults(): void
    {
        $inventoryId = $this->makeInventoryId();
        $productId = $this->makeProductId();

        $invProd = InventoryProduct::create([
            'inventory_id' => $inventoryId,
            'product_id' => $productId,
        ]);

        $this->assertDatabaseHas('inventory_products', [
            'id' => $invProd->id,
            'inventory_id' => $inventoryId,
            'product_id' => $productId,
            'stock_initial' => 0,
            'entries' => 0,
            'exits' => 0,
        ]);

        $this->assertSame(0, $invProd->fresh()->existence);
    }

    #[Test]
    public function existence_is_calculated_correctly(): void
    {
        $inventoryId = $this->makeInventoryId();
        $productId = $this->makeProductId();

        $invProd = InventoryProduct::create([
            'inventory_id' => $inventoryId,
            'product_id' => $productId,
            'stock_initial' => 10,
            'entries' => 4,
            'exits' => 3,
        ]);

        $this->assertSame(11, $invProd->fresh()->existence); // 10 + 4 - 3
    }

    #[Test]
    public function not_allow_duplicate_inventory_product_pair(): void
    {
        $inventoryId = $this->makeInventoryId();
        $productId = $this->makeProductId();

        InventoryProduct::create([
            'inventory_id' => $inventoryId,
            'product_id' => $productId,
        ]);

        $this->expectException(QueryException::class);

        InventoryProduct::create([
            'inventory_id' => $inventoryId,
            'product_id' => $productId, // unique compuesto
        ]);
    }
}
