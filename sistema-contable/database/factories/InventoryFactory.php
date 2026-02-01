<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'name' => $this->faker->word(),
        ];
    }
}