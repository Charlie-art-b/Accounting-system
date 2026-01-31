<?php

namespace Tests\Feature\Supplier;

use Tests\TestCase;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class SupplierDeleteSimpleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_delete_supplier_without_dependencies(): void
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Proveedor Eliminable',
            'identificacion' => 'D001',
            'correo' => 'delete1@test.com',
            'telefono' => null,
            'estado' => 'activo',
        ]);

        $supplier->delete();

        $this->assertDatabaseMissing('suppliers', [
            'identificacion' => 'D001',
        ]);
    }

    #[Test]
    public function deleted_supplier_is_not_found_after_removal(): void
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'empresa',
            'nombre_razon_social' => 'Proveedor Borrado',
            'identificacion' => 'D002',
            'correo' => 'delete2@test.com',
            'telefono' => null,
            'estado' => 'activo',
        ]);

        $id = $supplier->id;

        $supplier->delete();

        $this->assertNull(Supplier::find($id));
    }

    #[Test]
    public function supplier_table_is_empty_after_deleting_last_record(): void
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Único Proveedor',
            'identificacion' => 'D003',
            'correo' => 'delete3@test.com',
            'telefono' => null,
            'estado' => 'activo',
        ]);

        $supplier->delete();

        $this->assertCount(0, Supplier::all());
    }
}
