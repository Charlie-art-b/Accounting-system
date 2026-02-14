<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\AccountReceivable;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountReceivableFactory extends Factory
{
    protected $model = AccountReceivable::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'invoice_number' => 'INV' . fake()->numberBetween(100, 999),
            'issue_date' => now(),
            'due_date' => now()->addDays(10),
            'description' => fake()->sentence(),
            'total_amount' => 500,
            'paid_amount' => 100,
            'status' => 'partial',
        ];
    }
}
