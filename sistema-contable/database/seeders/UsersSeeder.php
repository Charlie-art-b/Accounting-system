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
            ['email' => 'administrador@gmail.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make(env('ADMIN_PASSWORD', '12345678')),
                'email_verified_at' => now(),
            ]
        );

        // Evita error si el rol no existe o si Spatie no está cargado
        if (method_exists($admin, 'assignRole')) {
            $admin->assignRole('administrador');
        }
    }
}