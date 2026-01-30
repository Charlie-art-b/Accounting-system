<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ProductCreateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function create_product_with_supplier()
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'individual',
            'nombre_razon_social' => 'Proveedor Test',
            'identificacion' => 'S001',
            'correo' => 'proveedor@test.com',
            'telefono' => '123456789',
            'estado' => 'active',
        ]);

        Product::create([
            'name' => 'Producto 1',
            'description' => 'Descripción del producto 1',
            'supplier_id' => $supplier->id,
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Producto 1',
            'supplier_id' => $supplier->id,
        ]);
    }

    #[Test]
    public function create_product_without_description()
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'legal_person',
            'nombre_razon_social' => 'Empresa Proveedora',
            'identificacion' => 'S002',
            'correo' => 'empresa@test.com',
            'telefono' => '987654321',
            'estado' => 'active',
        ]);

        Product::create([
            'name' => 'Producto 2',
            'supplier_id' => $supplier->id,
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Producto 2',
            'supplier_id' => $supplier->id,
        ]);
    }
}