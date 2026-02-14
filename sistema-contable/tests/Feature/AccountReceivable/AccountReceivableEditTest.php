<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AccountReceivable;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class AccountReceivableEditTest extends TestCase
{
    use RefreshDatabase;

    protected function account(): AccountReceivable
    {
        $customer = Customer::factory()->create();

        return AccountReceivable::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-EDIT',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Cuenta base QA',
            'total_amount' => 1000,
            'paid_amount' => 0,
        ]);
    }

    #[Test]
    public function edit_invoice_number()
    {
        $a = $this->account();
        $a->update(['invoice_number' => 'INV-NEW']);

        $this->assertEquals('INV-NEW', $a->fresh()->invoice_number);
    }

    #[Test]
    public function edit_description()
    {
        $a = $this->account();
        $a->update(['description' => 'Nueva descripcion']);

        $this->assertEquals('Nueva descripcion', $a->fresh()->description);
    }

    #[Test]
    public function edit_total_amount()
    {
        $a = $this->account();
        $a->update(['total_amount' => 1500]);

        $this->assertEquals(1500, $a->fresh()->total_amount);
    }

    #[Test]
    public function edit_paid_amount_updates_status_to_partial()
    {
        $a = $this->account();
        $a->update(['paid_amount' => 400]);

        $this->assertEquals('partial', $a->fresh()->status);
    }

    #[Test]
    public function edit_paid_amount_updates_status_to_paid()
    {
        $a = $this->account();
        $a->update(['paid_amount' => 1000]);

        $this->assertEquals('paid', $a->fresh()->status);
    }

    #[Test]
    public function negative_amounts_are_corrected_on_update()
    {
        $a = $this->account();
        $a->update([
            'total_amount' => -50,
            'paid_amount' => -10,
        ]);

        $this->assertEquals(0, $a->fresh()->total_amount);
        $this->assertEquals(0, $a->fresh()->paid_amount);
    }

    #[Test]
    public function paid_amount_cannot_exceed_total_on_update()
    {
        $a = $this->account();
        $a->update([
            'total_amount' => 500,
            'paid_amount' => 800,
        ]);

        $this->assertEquals(500, $a->fresh()->paid_amount);
    }
}
