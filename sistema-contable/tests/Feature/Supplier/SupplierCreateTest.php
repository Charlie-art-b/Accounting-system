<?php

namespace Tests\Feature\Supplier;

use Tests\TestCase;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use PHPUnit\Framework\Attributes\Test;

class SupplierCreateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function create_supplier_persona(): void
    {
        Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Juan perez',
            'identificacion' => 'S001',
            'correo' => 'juan@test.com',
            'telefono' => '88887777',
            'estado' => 'activo',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'identificacion' => 'S001',
            'tipo_proveedor' => 'persona',
            'estado' => 'activo',
        ]);
    }

    #[Test]
    public function create_supplier_empresa(): void
    {
        Supplier::create([
            'tipo_proveedor' => 'empresa',
            'nombre_razon_social' => 'Empresa ABC',
            'identificacion' => 'S002',
            'correo' => 'empresa@test.com',
            'telefono' => null,
            'estado' => 'activo',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'identificacion' => 'S002',
            'tipo_proveedor' => 'empresa',
        ]);
    }

    #[Test]
    public function correo_is_saved_in_lowercase(): void
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Ana',
            'identificacion' => 'S003',
            'correo' => 'ANA@TEST.COM',
            'telefono' => null,
            'estado' => 'activo',
        ]);

        $this->assertEquals('ana@test.com', $supplier->correo);
    }

    #[Test]
    public function nombre_razon_social_is_capitalized(): void
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'juan perez',
            'identificacion' => 'S004',
            'correo' => 'jp@test.com',
            'telefono' => null,
            'estado' => 'activo',
        ]);

        $this->assertEquals('Juan perez', $supplier->nombre_razon_social);
    }

    #[Test]
    public function telefono_can_be_null(): void
    {
        Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Sin Telefono',
            'identificacion' => 'S005',
            'correo' => 'sintelefono@test.com',
            // telefono omitido -> null
            'estado' => 'activo',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'identificacion' => 'S005',
        ]);
    }

    #[Test]
    public function tipo_proveedor_is_persona_by_default(): void
    {
        $supplier = Supplier::create([
            'nombre_razon_social' => 'Default Tipo',
            'identificacion' => 'S006',
            'correo' => 'defaulttipo@test.com',
            'telefono' => null,
            // tipo_proveedor omitido -> default persona (migración)
            'estado' => 'activo',
        ]);

        $this->assertEquals('persona', $supplier->fresh()->tipo_proveedor);
    }

    #[Test]
    public function estado_is_activo_by_default(): void
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'empresa',
            'nombre_razon_social' => 'Default Estado',
            'identificacion' => 'S007',
            'correo' => 'defaultestado@test.com',
            'telefono' => null,
            // estado omitido -> default activo (migración)
        ]);

        $this->assertEquals('activo', $supplier->fresh()->estado);
    }

    #[Test]
    public function not_allow_duplicate_identificacion(): void
    {
        Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'A',
            'identificacion' => 'S008',
            'correo' => 'a@test.com',
            'telefono' => null,
            'estado' => 'activo',
        ]);

        $this->expectException(QueryException::class);

        Supplier::create([
            'tipo_proveedor' => 'empresa',
            'nombre_razon_social' => 'B',
            'identificacion' => 'S008', // duplicada
            'correo' => 'b@test.com',
            'telefono' => null,
            'estado' => 'activo',
        ]);
    }

    #[Test]
    public function not_allow_duplicate_correo_even_if_case_changes(): void
    {
        Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'C',
            'identificacion' => 'S009',
            'correo' => 'DUP@TEST.COM',
            'telefono' => null,
            'estado' => 'activo',
        ]);

        $this->expectException(QueryException::class);

        Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'D',
            'identificacion' => 'S010',
            'correo' => 'dup@test.com', // mismo correo
            'telefono' => null,
            'estado' => 'activo',
        ]);
    }
}
