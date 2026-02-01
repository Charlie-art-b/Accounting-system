<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class CustomerDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function customer(): Customer
    {
        return Customer::create([
            'name' => 'Cliente',
            'first_last_name' => 'Base',
            'id_type' => 'identification',
            'identification' => 'D001',
            'email' => 'base@test.com',
            'customer_type' => 'individual',
            'status' => true,
        ]);
    }

    #[Test]
    public function deactivate_customer()
    {
        $c = $this->customer();
        $c->update(['status' => false]);

        $this->assertFalse($c->fresh()->status);
        $this->assertDatabaseHas('customers', [
            'identification' => 'D001',
            'status' => false,
        ]);
    }

    #[Test]
    public function deactivate_customer_using_scope()
    {
        $c = $this->customer();
        $c->update(['status' => false]);

        $activeCount = Customer::active()->count();
        $inactiveCount = Customer::inactive()->count();

        $this->assertEquals(0, $activeCount);
        $this->assertEquals(1, $inactiveCount);
    }

    #[Test]
    public function reactivate_customer()
    {
        $c = $this->customer();
        $c->update(['status' => false]);
        $c->update(['status' => true]);

        $this->assertTrue($c->fresh()->status);
    }
}