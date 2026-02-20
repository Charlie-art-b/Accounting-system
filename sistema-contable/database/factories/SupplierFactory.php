<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'country' => 'Colombia',
            'tax_id' => $this->faker->numerify('##.###.###-#'),
            'payment_terms' => $this->faker->randomElement(['30 días', '60 días', '90 días', 'Contado']),
            'is_active' => true,
        ];
    }
}
?>
