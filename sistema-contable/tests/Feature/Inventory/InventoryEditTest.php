<?php

namespace Tests\Feature\Inventories;

use Tests\TestCase;
use App\Models\Inventory;
use App\Models\Customer;
use App\Models\Product;
use App\Models\InventoryProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use PHPUnit\Framework\Attributes\Test;

class InventoryEditTest extends TestCase
{
    use RefreshDatabase;

    protected function createInventory(): Inventory
    {
        $customer = Customer::factory()->create();

        return Inventory::create([
            'customer_id' => $customer->id,
            'name' => 'Inventario Inicial',
        ]);
    }

    #[Test]
    public function edit_inventory_name()
    {
        $inventory = $this->createInventory();

        $inventory->update(['name' => 'Inventario Editado']);

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'name' => 'Inventario Editado',
        ]);
    }

    #[Test]
    public function edit_inventory_customer()
    {
        $inventory = $this->createInventory();
        $newCustomer = Customer::factory()->create();

        $inventory->update(['customer_id' => $newCustomer->id]);

        $this->assertEquals($newCustomer->id, $inventory->fresh()->customer_id);
    }

    #[Test]
    public function inventory_belongs_to_customer()
    {
        $inventory = $this->createInventory();

        $this->assertInstanceOf(Customer::class, $inventory->customer);
    }

    #[Test]
    public function inventory_can_have_products()
    {
        $inventory = $this->createInventory();
        $product = Product::factory()->create();

        InventoryProduct::create([
            'inventory_id' => $inventory->id,
            'product_id' => $product->id,
            'stock_initial' => 10,
        ]);

        $this->assertCount(1, $inventory->inventoryProducts);
    }

    #[Test]
    public function product_stock_is_calculated_correctly()
    {
        $inventory = $this->createInventory();
        $product = Product::factory()->create();

        $ip = InventoryProduct::create([
            'inventory_id' => $inventory->id,
            'product_id' => $product->id,
            'stock_initial' => 10,
            'entries' => 5,
            'exits' => 3,
        ]);

        $this->assertEquals(12, $ip->existence);
    }

    #[Test]
    public function register_entry_increases_entries()
    {
        $inventory = $this->createInventory();
        $product = Product::factory()->create();

        InventoryProduct::create([
            'inventory_id' => $inventory->id,
            'product_id' => $product->id,
        ]);

        InventoryProduct::registerEntry($inventory->id, $product->id, 7);

        $this->assertEquals(7, InventoryProduct::first()->entries);
    }

    #[Test]
    public function register_exit_increases_exits()
    {
        $inventory = $this->createInventory();
        $product = Product::factory()->create();

        InventoryProduct::create([
            'inventory_id' => $inventory->id,
            'product_id' => $product->id,
            'stock_initial' => 10,
        ]);

        InventoryProduct::registerExit($inventory->id, $product->id, 4);

        $this->assertEquals(4, InventoryProduct::first()->exits);
    }

    #[Test]
    public function cannot_register_exit_with_insufficient_stock()
    {
        $this->expectException(\Exception::class);

        $inventory = $this->createInventory();
        $product = Product::factory()->create();

        InventoryProduct::create([
            'inventory_id' => $inventory->id,
            'product_id' => $product->id,
            'stock_initial' => 2,
        ]);

        InventoryProduct::registerExit($inventory->id, $product->id, 5);
    }

    #[Test]
    public function inventory_products_are_deleted_when_inventory_is_deleted()
    {
        $inventory = $this->createInventory();
        $product = Product::factory()->create();

        InventoryProduct::create([
            'inventory_id' => $inventory->id,
            'product_id' => $product->id,
        ]);

        $inventory->delete();

        $this->assertDatabaseCount('inventory_products', 0);
    }

    #[Test]
    public function cannot_duplicate_product_in_same_inventory()
    {
        $this->expectException(QueryException::class);

        $inventory = $this->createInventory();
        $product = Product::factory()->create();

        InventoryProduct::create([
            'inventory_id' => $inventory->id,
            'product_id' => $product->id,
        ]);

        InventoryProduct::create([
            'inventory_id' => $inventory->id,
            'product_id' => $product->id,
        ]);
    }
}