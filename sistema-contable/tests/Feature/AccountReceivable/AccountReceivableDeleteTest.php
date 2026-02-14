<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AccountReceivable;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class AccountReceivableDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function account(): AccountReceivable
    {
        $customer = Customer::factory()->create();

        return AccountReceivable::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-BASE',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Cuenta base QA',
            'total_amount' => 1000,
            'paid_amount' => 0,
        ]);
    }

    #[Test]
    public function mark_account_as_paid()
    {
        $account = $this->account();

        $account->update([
            'paid_amount' => 1000
        ]);

        $this->assertEquals('paid', $account->fresh()->status);

        $this->assertDatabaseHas('accounts_receivable', [
            'invoice_number' => 'INV-BASE',
            'status' => 'paid',
        ]);
    }

    #[Test]
    public function mark_account_as_partial()
    {
        $account = $this->account();

        $account->update([
            'paid_amount' => 500
        ]);

        $this->assertEquals('partial', $account->fresh()->status);
    }

    #[Test]
    public function return_to_pending_when_paid_amount_zero()
    {
        $account = $this->account();

        $account->update(['paid_amount' => 500]);
        $account->update(['paid_amount' => 0]);

        $this->assertEquals('pending', $account->fresh()->status);
    }
}
