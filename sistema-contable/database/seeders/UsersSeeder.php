<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@sistema.com'],
            [
                'name' => 'Administrador',
                'password' => '1234',
                'email_verified_at' => now(),
            ]
        );

        $manager = User::updateOrCreate(
            ['email' => 'gerente@sistema.com'],
            [
                'name' => 'Gerente General',
                'password' => '1234',
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('administrador');
        $manager->assignRole('gerente');
    }
}
