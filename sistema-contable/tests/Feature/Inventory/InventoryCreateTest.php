<?php

namespace Tests\Feature\Inventory;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class InventoryCreateTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomer(): Customer
    {
        return Customer::create([
            'name' => 'Cliente',
            'first_last_name' => 'Test',
            'id_type' => 'identification',
            'identification' => fake()->unique()->numerify('C###'),
            'email' => fake()->unique()->safeEmail(),
            'customer_type' => 'individual',
        ]);
    }

    private function createProduct(): Product
    {
        return Product::create([
            'name' => fake()->word(),
            'description' => fake()->sentence(),
        ]);
    }

    #[Test]
    public function can_create_inventory()
    {
        $customer = $this->createCustomer();

        $inventory = Inventory::create([
            'customer_id' => $customer->id,
            'name' => 'Inventario Principal',
        ]);

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'customer_id' => $customer->id,
        ]);
    }

    #[Test]
    public function inventory_belongs_to_customer()
    {
        $customer = $this->createCustomer();

        $inventory = Inventory::create([
            'customer_id' => $customer->id,
            'name' => 'Inventario Cliente',
        ]);

        $this->assertEquals($customer->id, $inventory->customer->id);
    }

    #[Test]
    public function can_create_inventory_with_initial_products()
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $inventory = Inventory::createWithInitialProducts(
            $customer->id,
            'Inventario con productos',
            [
                [
                    'product_id' => $product->id,
                    'stock_initial' => 10,
                ]
            ]
        );

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
        ]);

        $this->assertDatabaseHas('inventory_products', [
            'inventory_id' => $inventory->id,
            'product_id' => $product->id,
            'stock_initial' => 10,
        ]);
    }

    #[Test]
    public function inventory_products_entries_and_exits_start_at_zero()
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        Inventory::createWithInitialProducts(
            $customer->id,
            'Inventario',
            [
                ['product_id' => $product->id]
            ]
        );

        $this->assertDatabaseHas('inventory_products', [
            'entries' => 0,
            'exits' => 0,
        ]);
    }

    #[Test]
    public function can_create_inventory_with_multiple_products()
    {
        $customer = $this->createCustomer();
        $products = Product::factory()->count(3)->create();

        $inventory = Inventory::createWithInitialProducts(
            $customer->id,
            'Inventario Multiple',
            $products->map(fn ($p) => [
                'product_id' => $p->id,
                'stock_initial' => 5,
            ])->toArray()
        );

        $this->assertCount(3, $inventory->inventoryProducts);
    }

    #[Test]
    public function inventory_is_not_created_if_product_fails()
    {
        $this->expectException(\Exception::class);

        $customer = $this->createCustomer();

        Inventory::createWithInitialProducts(
            $customer->id,
            'Inventario Fallido',
            [
                ['product_id' => 9999] // producto inexistente
            ]
        );

        $this->assertDatabaseMissing('inventories', [
            'name' => 'Inventario Fallido',
        ]);
    }

    #[Test]
    public function inventory_products_are_linked_correctly()
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $inventory = Inventory::createWithInitialProducts(
            $customer->id,
            'Inventario',
            [['product_id' => $product->id]]
        );

        $inventoryProduct = InventoryProduct::first();

        $this->assertEquals($inventory->id, $inventoryProduct->inventory_id);
        $this->assertEquals($product->id, $inventoryProduct->product_id);
    }

    #[Test]
    public function inventory_can_be_created_without_initial_products()
    {
        $customer = $this->createCustomer();

        $inventory = Inventory::createWithInitialProducts(
            $customer->id,
            'Inventario vacío',
            []
        );

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
        ]);

        $this->assertCount(0, $inventory->inventoryProducts);
    }

    #[Test]
    public function inventory_name_is_saved_correctly()
    {
        $customer = $this->createCustomer();

        $inventory = Inventory::create([
            'customer_id' => $customer->id,
            'name' => 'Bodega Central',
        ]);

        $this->assertEquals('Bodega Central', $inventory->name);
    }

    #[Test]
    public function inventory_has_timestamps()
    {
        $customer = $this->createCustomer();

        $inventory = Inventory::create([
            'customer_id' => $customer->id,
            'name' => 'Inventario',
        ]);

        $this->assertNotNull($inventory->created_at);
        $this->assertNotNull($inventory->updated_at);
    }
}