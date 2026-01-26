<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'tipo_proveedor' => 'empresa',
                'nombre_razon_social' => 'Distribuidora de Materiales S.A.',
                'identificacion' => '3101234567',
                'correo' => 'contacto@distribuidora.cr',
                'telefono' => '2234-5678',
                'estado' => 'activo',
            ],
            [
                'tipo_proveedor' => 'empresa',
                'nombre_razon_social' => 'Suministros Contables Costa Rica',
                'identificacion' => '3102345678',
                'correo' => 'info@suministroscontables.cr',
                'telefono' => '8765-4321',
                'estado' => 'activo',
            ],
            [
                'tipo_proveedor' => 'persona',
                'nombre_razon_social' => 'Juan Carlos Ramírez López',
                'identificacion' => '208450123',
                'correo' => 'juan.ramirez@ejemplo.cr',
                'telefono' => '8765-5432',
                'estado' => 'activo',
            ],
            [
                'tipo_proveedor' => 'persona',
                'nombre_razon_social' => 'María Elena Solís Pérez',
                'identificacion' => '207890456',
                'correo' => 'maria.solis@ejemplo.cr',
                'telefono' => '8765-6543',
                'estado' => 'activo',
            ],
            [
                'tipo_proveedor' => 'empresa',
                'nombre_razon_social' => 'Importadora de Equipos Ofimáticos',
                'identificacion' => '3103456789',
                'correo' => 'ventas@importadora-eq.cr',
                'telefono' => '2223-3333',
                'estado' => 'activo',
            ],
            [
                'tipo_proveedor' => 'empresa',
                'nombre_razon_social' => 'Servicios Contables Innovadores S.A.',
                'identificacion' => '3104567890',
                'correo' => 'soporte@servcontables.cr',
                'telefono' => '2333-4444',
                'estado' => 'inactivo',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(
                ['identificacion' => $supplier['identificacion']],
                $supplier
            );
        }
    }
}
