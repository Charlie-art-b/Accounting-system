<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ProductListTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function list_all_products()
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
            'description' => 'Descripción 1',
            'supplier_id' => $supplier->id,
        ]);

        Product::create([
            'name' => 'Producto 2',
            'description' => 'Descripción 2',
            'supplier_id' => $supplier->id,
        ]);

        $products = Product::all();

        $this->assertCount(2, $products);
        $this->assertEquals('Producto 1', $products->first()->name);
    }

    #[Test]
    public function list_products_by_supplier()
    {
        $supplier1 = Supplier::create([
            'tipo_proveedor' => 'individual',
            'nombre_razon_social' => 'Proveedor 1',
            'identificacion' => 'S001',
            'correo' => 'prov1@test.com',
            'telefono' => '111111111',
            'estado' => 'active',
        ]);

        $supplier2 = Supplier::create([
            'tipo_proveedor' => 'legal_person',
            'nombre_razon_social' => 'Empresa 1',
            'identificacion' => 'S002',
            'correo' => 'emp1@test.com',
            'telefono' => '222222222',
            'estado' => 'active',
        ]);

        Product::create([
            'name' => 'Producto A',
            'description' => 'Desc A',
            'supplier_id' => $supplier1->id,
        ]);

        Product::create([
            'name' => 'Producto B',
            'description' => 'Desc B',
            'supplier_id' => $supplier2->id,
        ]);

        $productsSupplier1 = Product::where('supplier_id', $supplier1->id)->get();
        $productsSupplier2 = Product::where('supplier_id', $supplier2->id)->get();

        $this->assertCount(1, $productsSupplier1);
        $this->assertCount(1, $productsSupplier2);
        $this->assertEquals('Producto A', $productsSupplier1->first()->name);
        $this->assertEquals('Producto B', $productsSupplier2->first()->name);
    }

    #[Test]
    public function list_products_ordered_by_name()
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
            'name' => 'Z Producto',
            'description' => 'Desc Z',
            'supplier_id' => $supplier->id,
        ]);

        Product::create([
            'name' => 'A Producto',
            'description' => 'Desc A',
            'supplier_id' => $supplier->id,
        ]);

        $products = Product::orderBy('name')->get();

        $this->assertEquals('A Producto', $products->first()->name);
        $this->assertEquals('Z Producto', $products->last()->name);
    }
}