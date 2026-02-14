<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\CollectionManagement;
use App\Models\AccountReceivable;
use App\Models\Customer;

class CollectionManagementCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_collection_management_record()
    {
        $account = AccountReceivable::factory()->create();
        $customer = Customer::factory()->create();

        $record = CollectionManagement::create([
            'account_receivable_id' => $account->id,
            'customer_id' => $customer->id,
            'reminder_attempts' => 1,
            'last_action' => 'Initial contact',
            'notes' => 'Test note',
        ]);

        $this->assertDatabaseHas('collection_managements', [
            'id' => $record->id,
        ]);
    }

    public function test_create_with_minimum_fields()
    {
        $account = AccountReceivable::factory()->create();
        $customer = Customer::factory()->create();

        $record = CollectionManagement::create([
            'account_receivable_id' => $account->id,
            'customer_id' => $customer->id,
        ]);

        $this->assertDatabaseHas('collection_managements', [
            'id' => $record->id,
        ]);
    }
}
