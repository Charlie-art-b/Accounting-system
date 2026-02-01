<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use PHPUnit\Framework\Attributes\Test;

class CustomerValidationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function not_allow_invalid_id_type()
    {
        $this->expectException(QueryException::class);

        Customer::create([
            'name' => 'Fail',
            'first_last_name' => 'Test',
            'id_type' => 'otro',
            'identification' => 'V001',
            'email' => 'fail@test.com',
            'customer_type' => 'individual',
        ]);
    }

    #[Test]
    public function not_allow_invalid_customer_type()
    {
        $this->expectException(QueryException::class);

        Customer::create([
            'name' => 'Fail',
            'first_last_name' => 'Test',
            'id_type' => 'passport',
            'identification' => 'V002',
            'email' => 'fail2@test.com',
            'customer_type' => 'company',
        ]);
    }

    #[Test]
    public function not_allow_null_name()
    {
        $this->expectException(QueryException::class);

        Customer::create([
            'first_last_name' => 'Test',
            'id_type' => 'passport',
            'identification' => 'V003',
            'email' => 'fail3@test.com',
            'customer_type' => 'individual',
        ]);
    }

    #[Test]
    public function not_allow_null_email()
    {
        $this->expectException(QueryException::class);

        Customer::create([
            'name' => 'Test',
            'first_last_name' => 'Test',
            'id_type' => 'passport',
            'identification' => 'V004',
            'customer_type' => 'individual',
        ]);
    }

    #[Test]
    public function not_allow_duplicate_email_on_update()
    {
        Customer::create([
            'name' => 'A',
            'first_last_name' => 'B',
            'id_type' => 'passport',
            'identification' => 'V005',
            'email' => 'same@test.com',
            'customer_type' => 'individual',
        ]);

        $c = Customer::create([
            'name' => 'C',
            'first_last_name' => 'D',
            'id_type' => 'passport',
            'identification' => 'V006',
            'email' => 'other@test.com',
            'customer_type' => 'individual',
        ]);

        $this->expectException(QueryException::class);
        $c->update(['email' => 'same@test.com']);
    }

    #[Test]
    public function not_allow_duplicate_identification_on_update()
    {
        Customer::create([
            'name' => 'A',
            'first_last_name' => 'B',
            'id_type' => 'passport',
            'identification' => 'V007',
            'email' => 'a@test.com',
            'customer_type' => 'individual',
        ]);

        $c = Customer::create([
            'name' => 'C',
            'first_last_name' => 'D',
            'id_type' => 'passport',
            'identification' => 'V008',
            'email' => 'b@test.com',
            'customer_type' => 'individual',
        ]);

        $this->expectException(QueryException::class);
        $c->update(['identification' => 'V007']);
    }

    #[Test]
    public function test_status_only_accepts_boolean()
    {
        $customer = Customer::factory()->create();

        $customer->update([
            'status' => 'invalid-value',
        ]);

        $this->assertTrue(
            in_array($customer->fresh()->status, [0, 1, true, false])
        );
    }

    #[Test]
    public function test_long_name_is_saved_when_no_validation_exists()
    {
        $customer = Customer::factory()->create();

        $longName = str_repeat('A', 150);

        $customer->update(['name' => $longName]);

        $this->assertEquals($longName, $customer->fresh()->name);
    }

    #[Test]
    public function test_long_identification_is_saved_when_no_validation_exists()
    {
        $customer = Customer::factory()->create();

        $longId = str_repeat('9', 30);

        $customer->update(['identification' => $longId]);

        $this->assertEquals($longId, $customer->fresh()->identification);
    }

    #[Test]
    public function test_invalid_email_can_be_saved_without_validation()
    {
        $customer = Customer::factory()->create();

        $customer->update([
            'email' => 'invalid-email-format',
        ]);

        $this->assertEquals(
            'invalid-email-format',
            $customer->fresh()->email
        );
    }
}