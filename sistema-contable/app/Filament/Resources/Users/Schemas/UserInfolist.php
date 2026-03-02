<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    private static function translateRole(string $role): string
    {
        $roles = [
            'administrador' => 'Administrador',
            'gerente' => 'Gerente',
            'sub-gerente' => 'Sub-gerente',
            'asistente' => 'Asistente',
        ];

        return $roles[$role] ?? $role;
    }

    private static function getModuleTranslations(): array
    {
        return [
            'users' => 'Usuarios',
            'roles' => 'Roles',
            'permissions' => 'Permisos',
            'accounting_accounts' => 'Cuentas Contables',
            'account_payables' => 'Cuentas por Pagar',
            'account_receivables' => 'Cuentas por Cobrar',
            'journal_entries' => 'Asientos Contables',
            'customers' => 'Clientes',
            'suppliers' => 'Proveedores',
            'collection_management' => 'Gestión de Cobranzas',
            'fixed_assets' => 'Activos Fijos',
            'inventories' => 'Inventarios',
            'inventory_products' => 'Inventario de Productos',
            'products' => 'Productos',
        ];
    }

    private static function getActionTranslations(): array
    {
        return [
            'view' => 'Ver',
            'create' => 'Crear',
            'update' => 'Actualizar',
            'delete' => 'Eliminar',
        ];
    }

    private static function groupPermissionsByModule($record): string
    {
        $permissions = $record->getAllPermissions()->pluck('name');
        $modules = self::getModuleTranslations();
        $actions = self::getActionTranslations();
        
        $grouped = [];
        
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission);
            if (count($parts) === 2) {
                $moduleKey = $parts[0];
                $actionKey = $parts[1];
                
                if (!isset($grouped[$moduleKey])) {
                    $grouped[$moduleKey] = [];
                }
                $grouped[$moduleKey][] = $actions[$actionKey] ?? $actionKey;
            }
        }
        
        if (empty($grouped)) {
            return 'Sin permisos';
        }
        
        $html = '<div style="display: flex; flex-direction: column; gap: 1rem;">';
        
        foreach ($grouped as $moduleKey => $moduleActions) {
            $moduleName = $modules[$moduleKey] ?? $moduleKey;
            $html .= '<div>';
            $html .= '<div style="font-weight: 600; color: rgb(107 114 128); margin-bottom: 0.25rem;">' . $moduleName . '</div>';
            $html .= '<div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">';
            
            foreach ($moduleActions as $action) {
                $html .= '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 500; background-color: rgb(243 244 246); color: rgb(55 65 81);">' . $action . '</span>';
            }
            
            $html .= '</div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Información Personal')
                    ->description('Datos básicos del usuario')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre completo'),

                        TextEntry::make('email')
                            ->label('Correo electrónico'),
                    ]),

                Section::make('Seguridad y Roles')
                    ->description('Permisos y validación de seguridad')
                    ->schema([
                        TextEntry::make('email_verified_at')
                            ->label('Correo verificado en')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('roles.name')
                            ->label('Rol(es)')
                            ->badge()
                            ->separator(', ')
                            ->placeholder('Sin rol')
                            ->formatStateUsing(fn ($state) => self::translateRole($state)),

                        TextEntry::make('permissions')
                            ->label('Permisos por Módulo')
                            ->state(fn ($record) => self::groupPermissionsByModule($record))
                            ->html()
                            ->placeholder('Sin permisos'),
                    ]),

                Section::make('Auditoría')
                    ->description('Registro de cambios')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Creado en')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Última actualización')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}