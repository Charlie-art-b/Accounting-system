<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AccountReceivable;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class AccountReceivableListTest extends TestCase
{
    use RefreshDatabase;

    private function customer()
    {
        return Customer::factory()->create();
    }

    #[Test]
    public function list_all_accounts_receivable()
    {
        $customer = $this->customer();

        AccountReceivable::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-L1',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Cuenta 1',
            'total_amount' => 100,
        ]);

        AccountReceivable::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-L2',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Cuenta 2',
            'total_amount' => 200,
        ]);

        $accounts = AccountReceivable::all();

        $this->assertCount(2, $accounts);
        $this->assertEquals('INV-L1', $accounts->first()->invoice_number);
    }

    #[Test]
    public function list_pending_accounts()
    {
        $customer = $this->customer();

        AccountReceivable::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-P1',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Pendiente',
            'total_amount' => 300,
        ]);

        AccountReceivable::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-P2',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Pagada',
            'total_amount' => 300,
            'paid_amount' => 300,
        ]);

        $pending = AccountReceivable::where('status', 'pending')->get();

        $this->assertCount(1, $pending);
        $this->assertEquals('pending', $pending->first()->status);
    }

    #[Test]
    public function list_paid_accounts()
    {
        $customer = $this->customer();

        AccountReceivable::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-PA1',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Pagada',
            'total_amount' => 200,
            'paid_amount' => 200,
        ]);

        $paid = AccountReceivable::where('status', 'paid')->get();

        $this->assertCount(1, $paid);
        $this->assertEquals('paid', $paid->first()->status);
    }

    #[Test]
    public function list_accounts_by_customer()
    {
        $customer1 = $this->customer();
        $customer2 = $this->customer();

        AccountReceivable::create([
            'customer_id' => $customer1->id,
            'invoice_number' => 'INV-C1',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Cliente 1',
            'total_amount' => 100,
        ]);

        AccountReceivable::create([
            'customer_id' => $customer2->id,
            'invoice_number' => 'INV-C2',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Cliente 2',
            'total_amount' => 100,
        ]);

        $accounts = AccountReceivable::where('customer_id', $customer1->id)->get();

        $this->assertCount(1, $accounts);
        $this->assertEquals($customer1->id, $accounts->first()->customer_id);
    }
}
