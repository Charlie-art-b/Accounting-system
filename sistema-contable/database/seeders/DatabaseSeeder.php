<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CustomerSeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,
            InventorySeeder::class,
            InventoryProductSeeder::class,
            AccountsReceivableSeeder::class,
            AccountPayableSeeder::class,
            CollectionManagementSeeder::class,
            FixedAssetsSeeder::class,
        ]);

 User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
            ]
        );
    }
}
