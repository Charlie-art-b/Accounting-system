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
use Illuminate\Support\Facades\Validator;

class InventoryValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function customer()
    {
        return Customer::factory()->create();
    }

    #[Test]
    public function inventory_name_cannot_be_null()
    {
        $this->expectException(QueryException::class);

        Inventory::create([
            'customer_id' => $this->customer()->id,
            'name' => null,
        ]);
    }

    #[Test]
    public function inventory_name_cannot_be_empty()
    {
        $data = [
            'customer_id' => Customer::factory()->create()->id,
            'name' => '',
        ];

        $validator = Validator::make($data, [
            'customer_id' => ['required', 'exists:customers,id'],
            'name' => ['required', 'string', 'min:1'],
        ]);

        $this->assertTrue($validator->fails());
    }

    #[Test]
    public function inventory_requires_customer()
    {
        $this->expectException(QueryException::class);

        Inventory::create([
            'customer_id' => null,
            'name' => 'Inventario inválido',
        ]);
    }

    #[Test]
    public function inventory_customer_must_exist()
    {
        $this->expectException(QueryException::class);

        Inventory::create([
            'customer_id' => 999,
            'name' => 'Inventario inválido',
        ]);
    }

    #[Test]
    public function product_must_exist_to_be_added_to_inventory()
    {
        $this->expectException(QueryException::class);

        $inventory = Inventory::create([
            'customer_id' => $this->customer()->id,
            'name' => 'Inventario',
        ]);

        InventoryProduct::create([
            'inventory_id' => $inventory->id,
            'product_id' => 999,
        ]);
    }

    #[Test]
    public function inventory_id_must_exist_for_inventory_product()
    {
        $this->expectException(QueryException::class);

        $product = Product::factory()->create();

        InventoryProduct::create([
            'inventory_id' => 999,
            'product_id' => $product->id,
        ]);
    }

    #[Test]
    public function stock_initial_cannot_be_negative()
    {
        $data = [
            'inventory_id' => Inventory::factory()->create()->id,
            'product_id' => Product::factory()->create()->id,
            'stock_initial' => -1,
            'entries' => 0,
            'exits' => 0,
        ];

        $validator = Validator::make($data, [
            'inventory_id' => ['required', 'exists:inventories,id'],
            'product_id' => ['required', 'exists:products,id'],
            'stock_initial' => ['required', 'integer', 'min:0'],
            'entries' => ['required', 'integer', 'min:0'],
            'exits' => ['required', 'integer', 'min:0'],
        ]);

        $this->assertTrue($validator->fails());
    }

    #[Test]
    public function entries_cannot_be_negative()
    {
        $data = [
            'inventory_id' => Inventory::factory()->create()->id,
            'product_id' => Product::factory()->create()->id,
            'stock_initial' => 0,
            'entries' => -5,
            'exits' => 0,
        ];

        $validator = Validator::make($data, [
            'entries' => ['required', 'integer', 'min:0'],
        ]);

        $this->assertTrue($validator->fails());
    }

    #[Test]
    public function exits_cannot_be_negative()
    {
        $data = [
            'inventory_id' => Inventory::factory()->create()->id,
            'product_id' => Product::factory()->create()->id,
            'stock_initial' => 0,
            'entries' => 0,
            'exits' => -3,
        ];

        $validator = Validator::make($data, [
            'exits' => ['required', 'integer', 'min:0'],
        ]);

        $this->assertTrue($validator->fails());
    }

    #[Test]
    public function cannot_register_exit_if_no_inventory_product_exists()
    {
        $this->expectException(\Exception::class);

        $inventory = Inventory::create([
            'customer_id' => $this->customer()->id,
            'name' => 'Inventario',
        ]);

        $product = Product::factory()->create();

        InventoryProduct::registerExit($inventory->id, $product->id, 1);
    }
}