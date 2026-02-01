<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Inventory;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class InventoryListTest extends TestCase
{
    use RefreshDatabase;

    protected function createInventory(string $name): Inventory
    {
        return Inventory::create([
            'customer_id' => Customer::factory()->create()->id,
            'name' => $name,
        ]);
    }

    #[Test]
    public function inventory_list_starts_empty()
    {
        $this->assertEquals(0, Inventory::count());
    }

    #[Test]
    public function inventory_can_be_listed()
    {
        $this->createInventory('Inv 1');

        $this->assertEquals(1, Inventory::count());
    }

    #[Test]
    public function multiple_inventories_are_listed()
    {
        $this->createInventory('Inv 1');
        $this->createInventory('Inv 2');

        $this->assertEquals(2, Inventory::count());
    }

    #[Test]
    public function inventory_names_are_correctly_stored()
    {
        $inventory = $this->createInventory('Inventario Central');

        $this->assertEquals('Inventario Central', $inventory->name);
    }

    #[Test]
    public function inventories_belong_to_customers()
    {
        $inventory = $this->createInventory('Inv Cliente');

        $this->assertNotNull($inventory->customer_id);
    }

    #[Test]
    public function inventory_has_customer_relationship()
    {
        $inventory = $this->createInventory('Inv Relación');

        $this->assertTrue($inventory->customer()->exists());
    }

    #[Test]
    public function inventory_list_returns_all_records()
    {
        $this->createInventory('Inv 1');
        $this->createInventory('Inv 2');
        $this->createInventory('Inv 3');

        $this->assertCount(3, Inventory::all());
    }

    #[Test]
    public function inventory_list_contains_expected_names()
    {
        $this->createInventory('Uno');
        $this->createInventory('Dos');

        $names = Inventory::pluck('name')->toArray();

        $this->assertContains('Uno', $names);
        $this->assertContains('Dos', $names);
    }

    #[Test]
    public function inventories_are_ordered_by_creation()
    {
        $first = $this->createInventory('Primero');
        $second = $this->createInventory('Segundo');

        $this->assertEquals(
            $first->id,
            Inventory::orderBy('created_at')->first()->id
        );
    }

    #[Test]
    public function inventory_list_does_not_include_deleted_records()
    {
        $inventory = $this->createInventory('Temporal');
        $inventory->delete();

        $this->assertEquals(0, Inventory::count());
    }
}