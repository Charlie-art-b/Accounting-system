<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AccountReceivable;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use PHPUnit\Framework\Attributes\Test;

class AccountReceivableValidationTest extends TestCase
{
    use RefreshDatabase;

    private function customer()
    {
        return Customer::factory()->create();
    }

    #[Test]
    public function not_allow_null_description()
    {
        $this->expectException(QueryException::class);

        AccountReceivable::create([
            'customer_id' => $this->customer()->id,
            'invoice_number' => 'INV-V1',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 100,
        ]);
    }

    #[Test]
    public function not_allow_null_invoice_number()
    {
        $this->expectException(QueryException::class);

        AccountReceivable::create([
            'customer_id' => $this->customer()->id,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Test QA',
            'total_amount' => 100,
        ]);
    }

    #[Test]
    public function paid_amount_cannot_exceed_total()
    {
        $account = AccountReceivable::create([
            'customer_id' => $this->customer()->id,
            'invoice_number' => 'INV-V2',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Monto excedido',
            'total_amount' => 100,
            'paid_amount' => 200,
        ]);

        $this->assertEquals(100, $account->paid_amount);
    }

    #[Test]
    public function negative_amounts_are_converted_to_zero()
    {
        $account = AccountReceivable::create([
            'customer_id' => $this->customer()->id,
            'invoice_number' => 'INV-V3',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Negativo',
            'total_amount' => -50,
            'paid_amount' => -10,
        ]);

        $this->assertEquals(0, $account->total_amount);
        $this->assertEquals(0, $account->paid_amount);
    }

    #[Test]
    public function status_is_set_correctly_when_paid()
    {
        $account = AccountReceivable::create([
            'customer_id' => $this->customer()->id,
            'invoice_number' => 'INV-V4',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Pagado',
            'total_amount' => 100,
            'paid_amount' => 100,
        ]);

        $this->assertEquals('paid', $account->status);
    }

    #[Test]
    public function status_is_partial_when_not_fully_paid()
    {
        $account = AccountReceivable::create([
            'customer_id' => $this->customer()->id,
            'invoice_number' => 'INV-V5',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Parcial',
            'total_amount' => 100,
            'paid_amount' => 40,
        ]);

        $this->assertEquals('partial', $account->status);
    }

    #[Test]
    public function long_description_is_saved_if_no_validation_exists()
    {
        $account = AccountReceivable::create([
            'customer_id' => $this->customer()->id,
            'invoice_number' => 'INV-V6',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => str_repeat('A', 200),
            'total_amount' => 100,
        ]);

        $this->assertNotNull($account->description);
    }

    #[Test]
    public function invalid_status_value_is_overwritten_by_model_logic()
    {
        $account = AccountReceivable::create([
            'customer_id' => $this->customer()->id,
            'invoice_number' => 'INV-V7',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Estado',
            'total_amount' => 100,
            'paid_amount' => 0,
            'status' => 'random',
        ]);

        $this->assertContains(
            $account->fresh()->status,
            ['pending', 'partial', 'paid']
        );
    }
}
