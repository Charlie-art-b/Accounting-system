<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\CollectionManagement;
use App\Models\AccountReceivable;
use App\Models\Customer;

class CollectionManagementEditTest extends TestCase
{
    use RefreshDatabase;

    private function createRecord()
    {
        return CollectionManagement::create([
            'account_receivable_id' => AccountReceivable::factory()->create()->id,
            'customer_id' => Customer::factory()->create()->id,
            'reminder_attempts' => 1,
            'last_action' => 'Reminder',
            'notes' => 'Initial note',
        ]);
    }

    public function test_edit_reminder_attempts()
    {
        $record = $this->createRecord();

        $record->update(['reminder_attempts' => 3]);

        $this->assertEquals(3, $record->fresh()->reminder_attempts);
    }

    public function test_edit_last_action()
    {
        $record = $this->createRecord();

        $record->update(['last_action' => 'Call made']);

        $this->assertEquals('Call made', $record->fresh()->last_action);
    }

    public function test_edit_notes()
    {
        $record = $this->createRecord();

        $record->update(['notes' => 'Updated note']);

        $this->assertEquals('Updated note', $record->fresh()->notes);
    }

    public function test_edit_next_reminder_date()
    {
        $record = $this->createRecord();

        $date = now()->addDays(5);

        $record->update(['next_reminder_at' => $date]);

        $this->assertEquals(
            $date->toDateTimeString(),
            $record->fresh()->next_reminder_at->toDateTimeString()
        );
    }
}
