<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::create([
            'name' => 'Cliente 1',
            'first_last_name' => 'Apellido',
            'id_type' => 'dimex',
            'identification' => '123456789',
            'email' => 'cliente1@ejemplo.com',
            'phone' => '88888888',
            'address' => 'San José, Costa Rica',
            'customer_type' => 'individual',
            'status' => true,
            'notes' => 'Este es el cliente 1',
        ]);
        Customer::create([
            'name' => 'Cliente 2',
            'first_last_name' => 'Apellido',
            'id_type' => 'identification',
            'identification' => '123456788',
            'email' => 'cliente2@ejemplo.com',
            'phone' => '88888887',
            'address' => 'San José, Costa Rica',
            'customer_type' => 'individual',
            'status' => true,
            'notes' => 'Este es el cliente 2',
        ]);
        Customer::create([
            'name' => 'Cliente 3',
            'first_last_name' => 'Apellido',
            'id_type' => 'identification',
            'identification' => '123456787',
            'email' => 'cliente3@ejemplo.com',
            'phone' => '88888886',
            'address' => 'San José, Costa Rica',
            'customer_type' => 'legal_person',
            'status' => false,
            'notes' => 'Este es el cliente 3',
        ]);
    }
}
