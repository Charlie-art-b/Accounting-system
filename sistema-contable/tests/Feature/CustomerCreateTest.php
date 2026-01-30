<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use PHPUnit\Framework\Attributes\Test;

class CustomerCreateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function create_customer_individual()
    {
        Customer::create([
            'name' => 'Juan',
            'first_last_name' => 'Perez',
            'id_type' => 'identification',
            'identification' => 'C001',
            'email' => 'juan@test.com',
            'customer_type' => 'individual',
        ]);

        $this->assertDatabaseHas('customers', [
            'identification' => 'C001',
            'customer_type' => 'individual',
        ]);
    }

    #[Test]
    public function create_customer_legal_person()
    {
        Customer::create([
            'name' => 'Empresa',
            'first_last_name' => 'SA',
            'id_type' => 'passport',
            'identification' => 'C002',
            'email' => 'empresa@test.com',
            'customer_type' => 'legal_person',
        ]);

        $this->assertDatabaseHas('customers', [
            'identification' => 'C002',
            'customer_type' => 'legal_person',
        ]);
    }

    #[Test]
    public function email_is_saved_in_lowercase()
    {
        $customer = Customer::create([
            'name' => 'Ana',
            'first_last_name' => 'Lopez',
            'id_type' => 'dimex',
            'identification' => 'C003',
            'email' => 'ANA@TEST.COM',
            'customer_type' => 'individual',
        ]);

        $this->assertEquals('ana@test.com', $customer->email);
    }

    #[Test]
    public function second_last_name_can_be_null()
    {
        Customer::create([
            'name' => 'Mario',
            'first_last_name' => 'Rojas',
            'id_type' => 'identification',
            'identification' => 'C004',
            'email' => 'mario@test.com',
            'customer_type' => 'individual',
        ]);

        $this->assertDatabaseHas('customers', ['identification' => 'C004']);
    }

    #[Test]
    public function phone_can_be_null()
    {
        Customer::create([
            'name' => 'Luis',
            'first_last_name' => 'Castro',
            'id_type' => 'passport',
            'identification' => 'C005',
            'email' => 'luis@test.com',
            'customer_type' => 'individual',
        ]);

        $this->assertDatabaseHas('customers', ['identification' => 'C005']);
    }

    #[Test]
    public function address_can_be_null()
    {
        Customer::create([
            'name' => 'Sofia',
            'first_last_name' => 'Diaz',
            'id_type' => 'identification',
            'identification' => 'C006',
            'email' => 'sofia@test.com',
            'customer_type' => 'individual',
        ]);

        $this->assertDatabaseHas('customers', ['identification' => 'C006']);
    }

    #[Test]
    public function notes_can_be_null()
    {
        Customer::create([
            'name' => 'Pedro',
            'first_last_name' => 'Mora',
            'id_type' => 'dimex',
            'identification' => 'C007',
            'email' => 'pedro@test.com',
            'customer_type' => 'individual',
        ]);

        $this->assertDatabaseHas('customers', ['identification' => 'C007']);
    }

    #[Test]
    public function status_is_true_by_default()
    {
        $customer = Customer::create([
            'name' => 'Laura',
            'first_last_name' => 'Vega',
            'id_type' => 'passport',
            'identification' => 'C008',
            'email' => 'laura@test.com',
            'customer_type' => 'individual',
        ]);

        $this->assertTrue($customer->fresh()->status);
    }

    #[Test]
    public function not_allow_duplicate_identification()
    {
        Customer::create([
            'name' => 'A',
            'first_last_name' => 'B',
            'id_type' => 'identification',
            'identification' => 'C009',
            'email' => 'a@test.com',
            'customer_type' => 'individual',
        ]);

        $this->expectException(QueryException::class);

        Customer::create([
            'name' => 'C',
            'first_last_name' => 'D',
            'id_type' => 'identification',
            'identification' => 'C009',
            'email' => 'b@test.com',
            'customer_type' => 'individual',
        ]);
    }

    #[Test]
    public function not_allow_duplicate_email()
    {
        Customer::create([
            'name' => 'X',
            'first_last_name' => 'Y',
            'id_type' => 'passport',
            'identification' => 'C010',
            'email' => 'dup@test.com',
            'customer_type' => 'individual',
        ]);

        $this->expectException(QueryException::class);

        Customer::create([
            'name' => 'Z',
            'first_last_name' => 'W',
            'id_type' => 'passport',
            'identification' => 'C011',
            'email' => 'dup@test.com',
            'customer_type' => 'individual',
        ]);
    }
}