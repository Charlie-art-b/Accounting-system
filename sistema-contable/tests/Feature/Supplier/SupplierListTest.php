<?php

namespace Tests\Feature\Supplier;

use Tests\TestCase;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class SupplierListTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function list_only_active_suppliers(): void
    {
        Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Activo',
            'identificacion' => 'L001',
            'correo' => 'activo@test.com',
            'estado' => 'activo',
        ]);

        Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Inactivo',
            'identificacion' => 'L002',
            'correo' => 'inactivo@test.com',
            'estado' => 'inactivo',
        ]);

        $this->assertCount(1, Supplier::activo()->get());
    }

    #[Test]
    public function list_suppliers_by_tipo_persona(): void
    {
        Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Persona',
            'identificacion' => 'L003',
            'correo' => 'persona@test.com',
            'estado' => 'activo',
        ]);

        Supplier::create([
            'tipo_proveedor' => 'empresa',
            'nombre_razon_social' => 'Empresa',
            'identificacion' => 'L004',
            'correo' => 'empresa@test.com',
            'estado' => 'activo',
        ]);

        $this->assertCount(1, Supplier::porTipo('persona')->get());
    }

    #[Test]
    public function find_supplier_by_identificacion(): void
    {
        Supplier::create([
            'tipo_proveedor' => 'empresa',
            'nombre_razon_social' => 'Buscar',
            'identificacion' => 'L005',
            'correo' => 'buscar@test.com',
            'estado' => 'activo',
        ]);

        $supplier = Supplier::porIdentificacion('L005');

        $this->assertNotNull($supplier);
        $this->assertEquals('L005', $supplier->identificacion);
    }
}
