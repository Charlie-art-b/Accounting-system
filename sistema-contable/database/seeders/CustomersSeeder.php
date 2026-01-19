<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customers;

class CustomersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customers::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '123-456-7890',
        ]);
    }
}
