<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName(),
            'first_last_name' => $this->faker->lastName(),
            'second_last_name' => $this->faker->optional()->lastName(),
            'id_type' => $this->faker->randomElement([
                'identification',
                'dimex',
                'passport',
            ]),
            'identification' => $this->faker->unique()->numerify('#########'),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->optional()->numerify('########'),
            'address' => $this->faker->optional()->address(),
            'customer_type' => $this->faker->randomElement([
                'individual',
                'legal_person',
            ]),
            'status' => true,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}