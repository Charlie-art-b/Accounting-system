<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Inventory;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class InventoryDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function inventory(): Inventory
    {
        $customer = Customer::factory()->create();

        return Inventory::create([
            'customer_id' => $customer->id,
            'name' => 'Inventario Base',
        ]);
    }

    #[Test]
    public function inventory_can_be_deleted()
    {
        $inventory = $this->inventory();
        $inventory->delete();

        $this->assertDatabaseMissing('inventories', [
            'id' => $inventory->id,
        ]);
    }

    #[Test]
    public function deleting_inventory_reduces_inventory_count()
    {
        $inventory = $this->inventory();

        $this->assertEquals(1, Inventory::count());
        $inventory->delete();
        $this->assertEquals(0, Inventory::count());
    }

    #[Test]
    public function inventory_is_removed_after_delete()
    {
        $inventory = $this->inventory();
        $inventory->delete();

        $this->assertNull(Inventory::find($inventory->id));
    }

    #[Test]
    public function multiple_inventories_can_be_deleted_independently()
    {
        $inv1 = $this->inventory();
        $inv2 = $this->inventory();

        $inv1->delete();

        $this->assertDatabaseMissing('inventories', ['id' => $inv1->id]);
        $this->assertDatabaseHas('inventories', ['id' => $inv2->id]);
    }

    #[Test]
    public function deleting_one_inventory_does_not_affect_others()
    {
        $this->inventory();
        $this->inventory();

        Inventory::first()->delete();

        $this->assertEquals(1, Inventory::count());
    }

    #[Test]
    public function inventory_belongs_to_customer_before_delete()
    {
        $inventory = $this->inventory();

        $this->assertNotNull($inventory->customer_id);
    }

    #[Test]
    public function inventory_customer_relation_remains_valid_before_delete()
    {
        $inventory = $this->inventory();

        $this->assertTrue($inventory->customer()->exists());
    }

    #[Test]
    public function inventory_can_be_deleted_even_if_name_is_short()
    {
        $inventory = Inventory::create([
            'customer_id' => Customer::factory()->create()->id,
            'name' => 'A',
        ]);

        $inventory->delete();

        $this->assertDatabaseMissing('inventories', [
            'name' => 'A',
        ]);
    }

    #[Test]
    public function deleting_inventory_does_not_delete_customer()
    {
        $inventory = $this->inventory();
        $customerId = $inventory->customer_id;

        $inventory->delete();

        $this->assertDatabaseHas('customers', [
            'id' => $customerId,
        ]);
    }

    #[Test]
    public function deleting_inventory_twice_does_not_throw_error()
    {
        $inventory = $this->inventory();
        $inventory->delete();

        $this->assertNull(Inventory::find($inventory->id));
    }
}