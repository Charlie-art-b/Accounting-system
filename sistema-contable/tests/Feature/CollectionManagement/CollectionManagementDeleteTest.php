<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\CollectionManagement;
use App\Models\AccountReceivable;
use App\Models\Customer;

class CollectionManagementDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_delete_collection_management_record()
    {
        $account = AccountReceivable::factory()->create();
        $customer = Customer::factory()->create();

        $record = CollectionManagement::create([
            'account_receivable_id' => $account->id,
            'customer_id' => $customer->id,
        ]);

        $record->delete();

        $this->assertDatabaseMissing('collection_managements', [
            'id' => $record->id,
        ]);
    }
}
