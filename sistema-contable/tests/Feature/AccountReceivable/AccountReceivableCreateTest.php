<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AccountReceivable;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class AccountReceivableCreateTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomer()
    {
        return Customer::factory()->create();
    }

    #[Test]
    public function create_account_receivable_successfully()
    {
        $customer = $this->createCustomer();

        AccountReceivable::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV001',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Cuenta QA',
            'total_amount' => 1000,
        ]);

        $this->assertDatabaseHas('accounts_receivable', [
            'invoice_number' => 'INV001',
        ]);
    }

    #[Test]
    public function default_paid_amount_is_zero_and_status_pending()
    {
        $customer = $this->createCustomer();

        $account = AccountReceivable::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV002',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Cuenta QA',
            'total_amount' => 500,
        ]);

        $this->assertEquals(0, $account->paid_amount);
        $this->assertEquals('pending', $account->status);
    }

    #[Test]
    public function paid_amount_cannot_exceed_total()
    {
        $customer = $this->createCustomer();

        $account = AccountReceivable::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV003',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Cuenta QA',
            'total_amount' => 100,
            'paid_amount' => 200,
        ]);

        $this->assertEquals(100, $account->paid_amount);
    }

    #[Test]
    public function negative_amounts_are_converted_to_zero()
    {
        $customer = $this->createCustomer();

        $account = AccountReceivable::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV004',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Cuenta QA',
            'total_amount' => -50,
            'paid_amount' => -10,
        ]);

        $this->assertEquals(0, $account->total_amount);
        $this->assertEquals(0, $account->paid_amount);
    }

    #[Test]
    public function status_becomes_paid_when_total_is_fully_paid()
    {
        $customer = $this->createCustomer();

        $account = AccountReceivable::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV005',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Cuenta QA',
            'total_amount' => 200,
            'paid_amount' => 200,
        ]);

        $this->assertEquals('paid', $account->status);
    }

    #[Test]
    public function status_becomes_partial_when_partially_paid()
    {
        $customer = $this->createCustomer();

        $account = AccountReceivable::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV006',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Cuenta QA',
            'total_amount' => 200,
            'paid_amount' => 50,
        ]);

        $this->assertEquals('partial', $account->status);
    }

    #[Test]
    public function pending_amount_accessor_works_correctly()
    {
        $customer = $this->createCustomer();

        $account = AccountReceivable::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV007',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Cuenta QA',
            'total_amount' => 300,
            'paid_amount' => 100,
        ]);

        $this->assertEquals(200, $account->pending_amount);
    }

    #[Test]
    public function cannot_delete_when_status_pending_or_partial()
    {
        $this->expectException(\Exception::class);

        $customer = $this->createCustomer();

        $account = AccountReceivable::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV008',
            'issue_date' => now(),
            'due_date' => now()->addMonth(),
            'description' => 'Cuenta por cobrar prueba',
            'total_amount' => 500,
        ]);

        $account->delete();
    }
}
