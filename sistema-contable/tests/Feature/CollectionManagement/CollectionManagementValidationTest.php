<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\CollectionManagement;
use App\Models\AccountReceivable;
use App\Models\Customer;

class CollectionManagementValidationTest extends TestCase
{
    use RefreshDatabase;

    private function createRecord($dueDate = null)
    {
        $account = AccountReceivable::factory()->create([
            'total_amount' => 1000,
            'paid_amount' => 200,
            'due_date' => $dueDate ?? now()->addDays(5),
        ]);

        return CollectionManagement::create([
            'account_receivable_id' => $account->id,
            'customer_id' => Customer::factory()->create()->id,
        ]);
    }

    public function test_pending_amount_calculates_correctly()
    {
        $record = $this->createRecord();

        $this->assertEquals(800, $record->pending_amount);
    }

    public function test_days_late_returns_zero_if_not_overdue()
    {
        $record = $this->createRecord();

        $this->assertEquals(0, $record->days_late);
    }

    public function test_status_returns_valid_value()
    {
        $record = $this->createRecord();

        $this->assertContains($record->status, [
            'pending',
            'due_soon',
            'overdue',
        ]);
    }

    public function test_title_accessor_returns_string()
    {
        $record = $this->createRecord();

        $this->assertIsString($record->title);
    }
}
