<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'permissions.view',
            'permissions.create',
            'permissions.update',
            'permissions.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        $admin = Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        $manager = Role::firstOrCreate(['name' => 'gerente', 'guard_name' => 'web']);
        $subManager = Role::firstOrCreate(['name' => 'sub-gerente', 'guard_name' => 'web']);
        $assistant = Role::firstOrCreate(['name' => 'asistente', 'guard_name' => 'web']);

        $admin->syncPermissions($permissions);

        $manager->syncPermissions([
            'users.view',
            'users.update',
            'roles.view',
            'permissions.view',
        ]);

        $subManager->syncPermissions([
            'users.view',
            'roles.view',
            'permissions.view',
        ]);

        $assistant->syncPermissions([
            'users.view',
        ]);
    }
}
