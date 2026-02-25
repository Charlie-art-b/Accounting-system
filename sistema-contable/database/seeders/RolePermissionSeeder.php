<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    
    private array $modules = [
        // Usuarios y control de acceso
        'users'               => ['view', 'create', 'update', 'delete'],
        'roles'               => ['view', 'create', 'update', 'delete'],
        'permissions'         => ['view', 'create', 'update', 'delete'],

        // Contabilidad
        'accounting_accounts' => ['view', 'create', 'update', 'delete'],
        'account_payables'    => ['view', 'create', 'update', 'delete'],
        'account_receivables' => ['view', 'create', 'update', 'delete'],
        'journal_entries'     => ['view', 'create', 'update', 'delete'],

        // Clientes y proveedores
        'customers'           => ['view', 'create', 'update', 'delete'],
        'suppliers'           => ['view', 'create', 'update', 'delete'],

        // Cobranzas
        'collection_management' => ['view', 'create', 'update', 'delete'],

        // Activos fijos
        'fixed_assets'        => ['view', 'create', 'update', 'delete'],

        // Inventario y productos
        'inventories'         => ['view', 'create', 'update', 'delete'],
        'inventory_products'  => ['view', 'create', 'update', 'delete'],
        'products'            => ['view', 'create', 'update', 'delete'],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $allPermissions = [];

        foreach ($this->modules as $module => $actions) {
            foreach ($actions as $action) {
                $name = "{$module}.{$action}";
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
                $allPermissions[] = $name;
            }
        }

        $admin      = Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        $manager    = Role::firstOrCreate(['name' => 'gerente',       'guard_name' => 'web']);
        $subManager = Role::firstOrCreate(['name' => 'sub-gerente',   'guard_name' => 'web']);
        $assistant  = Role::firstOrCreate(['name' => 'asistente',     'guard_name' => 'web']);

        $admin->syncPermissions($allPermissions);

        $manager->syncPermissions(
            $this->resolvePermissions([
                'users'                 => ['view', 'create', 'update'],
                'roles'                 => ['view'],
                'permissions'           => ['view'],
                'accounting_accounts'   => ['view', 'create', 'update'],
                'account_payables'      => ['view', 'create', 'update'],
                'account_receivables'   => ['view', 'create', 'update'],
                'journal_entries'       => ['view', 'create', 'update'],
                'customers'             => ['view', 'create', 'update'],
                'suppliers'             => ['view', 'create', 'update'],
                'collection_management' => ['view', 'create', 'update'],
                'fixed_assets'          => ['view', 'create', 'update'],
                'inventories'           => ['view', 'create', 'update'],
                'inventory_products'    => ['view', 'create', 'update'],
                'products'              => ['view', 'create', 'update'],
            ])
        );

        $subManager->syncPermissions(
            $this->resolvePermissions([
                'users'                 => ['view'],
                'roles'                 => ['view'],
                'permissions'           => ['view'],
                'accounting_accounts'   => ['view'],
                'account_payables'      => ['view', 'create', 'update'],
                'account_receivables'   => ['view', 'create', 'update'],
                'journal_entries'       => ['view'],
                'customers'             => ['view', 'create', 'update'],
                'suppliers'             => ['view', 'create', 'update'],
                'collection_management' => ['view', 'create', 'update'],
                'fixed_assets'          => ['view'],
                'inventories'           => ['view', 'create', 'update'],
                'inventory_products'    => ['view', 'create', 'update'],
                'products'              => ['view', 'create', 'update'],
            ])
        );

        $assistant->syncPermissions(
            $this->resolvePermissions([
                'users'                 => ['view'],
                'accounting_accounts'   => ['view'],
                'account_payables'      => ['view'],
                'account_receivables'   => ['view'],
                'journal_entries'       => ['view'],
                'customers'             => ['view'],
                'suppliers'             => ['view'],
                'collection_management' => ['view'],
                'fixed_assets'          => ['view'],
                'inventories'           => ['view'],
                'inventory_products'    => ['view'],
                'products'              => ['view'],
            ])
        );
    }

  
    private function resolvePermissions(array $map): array
    {
        $permissions = [];

        foreach ($map as $module => $actions) {
            foreach ($actions as $action) {
                $permissions[] = "{$module}.{$action}";
            }
        }

        return $permissions;
    }
}