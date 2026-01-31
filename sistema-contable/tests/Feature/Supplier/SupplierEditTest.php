<?php

namespace Tests\Feature\Supplier;

use Tests\TestCase;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use PHPUnit\Framework\Attributes\Test;

class SupplierEditTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function edit_supplier_basic_data(): void
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Proveedor Inicial',
            'identificacion' => 'E001',
            'correo' => 'inicial@test.com',
            'telefono' => '1111',
            'estado' => 'activo',
        ]);

        $supplier->update([
            'nombre_razon_social' => 'Proveedor Editado',
            'telefono' => '2222',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'identificacion' => 'E001',
            'telefono' => '2222',
        ]);
    }

    #[Test]
    public function correo_is_converted_to_lowercase_on_update(): void
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'empresa',
            'nombre_razon_social' => 'Empresa',
            'identificacion' => 'E002',
            'correo' => 'EMPRESA@TEST.COM',
            'telefono' => null,
            'estado' => 'activo',
        ]);

        $supplier->update([
            'correo' => 'NUEVO@MAIL.COM',
        ]);

        $this->assertEquals('nuevo@mail.com', $supplier->fresh()->correo);
    }

    #[Test]
    public function nombre_is_capitalized_on_update(): void
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'nombre viejo',
            'identificacion' => 'E003',
            'correo' => 'nombre@test.com',
            'telefono' => null,
            'estado' => 'activo',
        ]);

        $supplier->update([
            'nombre_razon_social' => 'nombre nuevo',
        ]);

        $this->assertEquals('Nombre nuevo', $supplier->fresh()->nombre_razon_social);
    }

    #[Test]
    public function not_allow_duplicate_correo_on_edit(): void
    {
        Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'A',
            'identificacion' => 'E004',
            'correo' => 'a@test.com',
            'telefono' => null,
            'estado' => 'activo',
        ]);

        $supplier = Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'B',
            'identificacion' => 'E005',
            'correo' => 'b@test.com',
            'telefono' => null,
            'estado' => 'activo',
        ]);

        $this->expectException(QueryException::class);

        $supplier->update([
            'correo' => 'A@TEST.COM',
        ]);
    }
}
