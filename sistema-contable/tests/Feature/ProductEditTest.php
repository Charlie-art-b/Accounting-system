<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ProductEditTest extends TestCase
{
    use RefreshDatabase;

    protected function product(): Product
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'individual',
            'nombre_razon_social' => 'Proveedor Base',
            'identificacion' => 'S003',
            'correo' => 'base@test.com',
            'telefono' => '555555555',
            'estado' => 'active',
        ]);

        return Product::create([
            'name' => 'Producto Base',
            'description' => 'Descripción base',
            'supplier_id' => $supplier->id,
        ]);
    }

    #[Test]
    public function edit_product_name()
    {
        $p = $this->product();
        $p->update(['name' => 'Nuevo Nombre']);
        $this->assertEquals('Nuevo Nombre', $p->fresh()->name);
    }

    #[Test]
    public function edit_product_description()
    {
        $p = $this->product();
        $p->update(['description' => 'Nueva descripción']);
        $this->assertEquals('Nueva descripción', $p->fresh()->description);
    }

    #[Test]
    public function edit_product_supplier()
    {
        $p = $this->product();
        $newSupplier = Supplier::create([
            'tipo_proveedor' => 'legal_person',
            'nombre_razon_social' => 'Nuevo Proveedor',
            'identificacion' => 'S004',
            'correo' => 'nuevo@test.com',
            'telefono' => '111111111',
            'estado' => 'active',
        ]);

        $p->update(['supplier_id' => $newSupplier->id]);
        $this->assertEquals($newSupplier->id, $p->fresh()->supplier_id);
    }
}