<?php

namespace Database\Seeders;
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
            RolePermissionSeeder::class,
            UsersSeeder::class,
            CustomerSeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,
            InventorySeeder::class,
            InventoryProductSeeder::class,
            AccountsReceivableSeeder::class,
            AccountPayableSeeder::class,
            CollectionManagementSeeder::class,
            FixedAssetsSeeder::class,
            AccountingAccountSeeder::class,
        ]);
    }
}
