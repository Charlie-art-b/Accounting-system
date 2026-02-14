<?php

namespace Database\Factories;

use App\Models\CollectionManagement;
use App\Models\Customer;
use App\Models\AccountReceivable;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollectionManagementFactory extends Factory
{
    protected $model = CollectionManagement::class;

    public function definition(): array
    {
        return [
            'account_receivable_id' => AccountReceivable::factory(),
            'customer_id' => Customer::factory(),
            'reminder_attempts' => 1,
            'last_action' => 'Initial contact',
            'notes' => 'Test note',
        ];
    }
}
