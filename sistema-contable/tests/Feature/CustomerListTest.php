<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class CustomerListTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function list_all_customers()
    {
        Customer::create([
            'name' => 'Juan',
            'first_last_name' => 'Perez',
            'id_type' => 'identification',
            'identification' => 'C001',
            'email' => 'juan@test.com',
            'customer_type' => 'individual',
            'status' => true,
        ]);

        Customer::create([
            'name' => 'Empresa',
            'first_last_name' => 'SA',
            'id_type' => 'passport',
            'identification' => 'C002',
            'email' => 'empresa@test.com',
            'customer_type' => 'legal_person',
            'status' => true,
        ]);

        $customers = Customer::all();

        $this->assertCount(2, $customers);
        $this->assertEquals('C001', $customers->first()->identification);
    }

    #[Test]
    public function list_active_customers()
    {
        Customer::create([
            'name' => 'Activo',
            'first_last_name' => 'Uno',
            'id_type' => 'identification',
            'identification' => 'A001',
            'email' => 'activo@test.com',
            'customer_type' => 'individual',
            'status' => true,
        ]);

        Customer::create([
            'name' => 'Inactivo',
            'first_last_name' => 'Dos',
            'id_type' => 'identification',
            'identification' => 'I001',
            'email' => 'inactivo@test.com',
            'customer_type' => 'individual',
            'status' => false,
        ]);

        $activeCustomers = Customer::active()->get();

        $this->assertCount(1, $activeCustomers);
        $this->assertEquals('A001', $activeCustomers->first()->identification);
    }

    #[Test]
    public function list_inactive_customers()
    {
        Customer::create([
            'name' => 'Activo',
            'first_last_name' => 'Uno',
            'id_type' => 'identification',
            'identification' => 'A002',
            'email' => 'activo2@test.com',
            'customer_type' => 'individual',
            'status' => true,
        ]);

        Customer::create([
            'name' => 'Inactivo',
            'first_last_name' => 'Dos',
            'id_type' => 'identification',
            'identification' => 'I002',
            'email' => 'inactivo2@test.com',
            'customer_type' => 'individual',
            'status' => false,
        ]);

        $inactiveCustomers = Customer::inactive()->get();

        $this->assertCount(1, $inactiveCustomers);
        $this->assertEquals('I002', $inactiveCustomers->first()->identification);
    }

    #[Test]
    public function list_customers_by_type()
    {
        Customer::create([
            'name' => 'Individual',
            'first_last_name' => 'Uno',
            'id_type' => 'identification',
            'identification' => 'IND001',
            'email' => 'ind@test.com',
            'customer_type' => 'individual',
            'status' => true,
        ]);

        Customer::create([
            'name' => 'Legal',
            'first_last_name' => 'Dos',
            'id_type' => 'passport',
            'identification' => 'LEG001',
            'email' => 'leg@test.com',
            'customer_type' => 'legal_person',
            'status' => true,
        ]);

        $individuals = Customer::byType('individual')->get();

        $this->assertCount(1, $individuals);
        $this->assertEquals('individual', $individuals->first()->customer_type);
    }
}