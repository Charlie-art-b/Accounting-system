<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Supplier;
use App\Models\Customer;
use PHPUnit\Framework\Attributes\Test;

class SupplierDeleteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function delete_supplier_with_no_dependencies()
    {
      $supplier = Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Proveedor a Eliminar',
            'identificacion' => '123456789',
            'correo' => 'eliminar@example.com',
            'estado' => 'activo',
        ]);

        $supplierId = $supplier->id;

        $supplier->delete();

        $this->assertDatabaseMissing('suppliers', [
            'id' => $supplierId,
        ]);

        $this->assertNull(Supplier::find($supplierId));
    }

    #[Test]
    public function delete_supplier_with_associated_customers()
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'empresa',
            'nombre_razon_social' => 'Proveedor con Clientes',
            'identificacion' => '987654321',
            'correo' => 'conclientes@example.com',
            'estado' => 'activo',
        ]);

        $customer1 = Customer::create([
            'name' => 'Cliente',
            'first_last_name' => 'Uno',
            'id_type' => 'identification',
            'identification' => 'CL001',
            'email' => 'cliente1@test.com',
            'customer_type' => 'individual',
            'status' => true,
        ]);

        $customer2 = Customer::create([
            'name' => 'Cliente',
            'first_last_name' => 'Dos',
            'id_type' => 'identification',
            'identification' => 'CL002',
            'email' => 'cliente2@test.com',
            'customer_type' => 'individual',
            'status' => true,
        ]);

        $supplier->customers()->attach([$customer1->id, $customer2->id]);

        $this->assertCount(2, $supplier->customers);

        $supplierId = $supplier->id;

        $supplier->delete();

        $this->assertDatabaseMissing('suppliers', ['id' => $supplierId]);

        $this->assertDatabaseHas('customers', ['id' => $customer1->id]);
        $this->assertDatabaseHas('customers', ['id' => $customer2->id]);
    }

    /*
    #[Test]
    public function delete_supplier_with_accounts_payable()
    {
        Implementar cuando exista el modelo
    }

    #[Test]
    public function delete_supplier_with_products()
    {
        Implementar cuando exista el modelo
    }
    */

    #[Test]
    public function delete_supplier_can_change_status_to_inactive_instead_of_deleting()
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'empresa',
            'nombre_razon_social' => 'Cambiar a Inactivo',
            'identificacion' => '951753456',
            'correo' => 'inactivar@example.com',
            'estado' => 'activo',
        ]);

        $this->assertEquals('activo', $supplier->estado);
        $supplier->update(['estado' => 'inactivo']);
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'estado' => 'inactivo',
        ]);

        $this->assertEquals('inactivo', $supplier->fresh()->estado);
    }

    #[Test]
    public function reactivate_inactive_supplier()
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Para Reactivar',
            'identificacion' => '753159753',
            'correo' => 'reactivar@example.com',
            'estado' => 'inactivo',
        ]);

        $this->assertEquals('inactivo', $supplier->estado);
        $supplier->update(['estado' => 'activo']);

        $this->assertEquals('activo', $supplier->fresh()->estado);
        $this->assertTrue(Supplier::activo()->get()->contains($supplier));
    }
}
