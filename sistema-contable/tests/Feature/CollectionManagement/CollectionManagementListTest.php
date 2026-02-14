<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\CollectionManagement;
use App\Models\AccountReceivable;
use App\Models\Customer;

class CollectionManagementListTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_collection_management_records()
    {
        CollectionManagement::withoutEvents(function () {
            CollectionManagement::create([
                'account_receivable_id' => AccountReceivable::factory()->create()->id,
                'customer_id' => Customer::factory()->create()->id,
            ]);
        });

        $this->assertDatabaseCount('collection_managements', 1);
    }

    public function test_retrieve_single_record()
    {
        $record = CollectionManagement::create([
            'account_receivable_id' => AccountReceivable::factory()->create()->id,
            'customer_id' => Customer::factory()->create()->id,
        ]);

        $this->assertNotNull(CollectionManagement::find($record->id));
    }
}
