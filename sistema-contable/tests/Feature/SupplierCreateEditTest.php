<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\QueryException;
use PHPUnit\Framework\Attributes\Test;

class SupplierCreateEditTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function create_type_supplier_person()
    {
        $data = [
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Juan Carlos García López',
            'identificacion' => '123456789',
            'correo' => 'juan.garcia@example.com',
            'telefono' => '88888888',
            'estado' => 'activo',
        ];

        Supplier::create($data);

        $this->assertDatabaseHas('suppliers', [
            'tipo_proveedor' => 'persona',
            'identificacion' => '123456789',
            'correo' => 'juan.garcia@example.com',
            'estado' => 'activo',
        ]);
    }

    #[Test]
    public function create_type_supplier_company()
    {
        $data = [
            'tipo_proveedor' => 'empresa',
            'nombre_razon_social' => 'Distribuidora XYZ S.A.',
            'identificacion' => '3101234567',
            'correo' => 'info@distribuidora.com',
            'telefono' => '22223333',
            'estado' => 'activo',
        ];

        Supplier::create($data);

        $this->assertDatabaseHas('suppliers', [
            'tipo_proveedor' => 'empresa',
            'nombre_razon_social' => 'Distribuidora XYZ S.A.',
            'identificacion' => '3101234567',
        ]);
    }

    #[Test]
    public function create_inactive_supplier()
    {
        $data = [
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Pedro Hernández',
            'identificacion' => '111222333',
            'correo' => 'pedro@example.com',
            'estado' => 'inactivo',
        ];

        Supplier::create($data);

        $this->assertDatabaseHas('suppliers', [
            'identificacion' => '111222333',
            'estado' => 'inactivo',
        ]);
    }

    #[Test]
    public function convert_email_to_lowercase_on_create()
    {
        $data = [
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Ana López',
            'identificacion' => '444555666',
            'correo' => 'ANA.LOPEZ@EXAMPLE.COM',
            'estado' => 'activo',
        ];

        $supplier = Supplier::create($data);

        $this->assertEquals('ana.lopez@example.com', $supplier->correo);
    }

    #[Test]
    public function associate_multiple_customers_on_supplier()
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'empresa',
            'nombre_razon_social' => 'Proveedor ABC',
            'identificacion' => '555666777',
            'correo' => 'abc@example.com',
            'estado' => 'activo',
        ]);

        $customer1 = Customer::create([
            'name' => 'Cliente',
            'first_last_name' => 'Uno',
            'id_type' => 'identification',
            'identification' => 'C001',
            'email' => 'cliente1@test.com',
            'customer_type' => 'individual',
            'status' => true,
        ]);

        $customer2 = Customer::create([
            'name' => 'Cliente',
            'first_last_name' => 'Dos',
            'id_type' => 'identification',
            'identification' => 'C002',
            'email' => 'cliente2@test.com',
            'customer_type' => 'individual',
            'status' => true,
        ]);

        $supplier->customers()->attach([$customer1->id, $customer2->id]);

        $this->assertCount(2, $supplier->customers);
        $this->assertTrue($supplier->customers->contains($customer1));
        $this->assertTrue($supplier->customers->contains($customer2));
    }

    #[Test]
    public function not_allow_create_duplicate_identification_supplier()
    {
        Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Proveedor Original',
            'identificacion' => '123456789',
            'correo' => 'original@example.com',
            'estado' => 'activo',
        ]);

        $this->expectException(QueryException::class);

        Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Proveedor Duplicado',
            'identificacion' => '123456789', // Duplicado
            'correo' => 'duplicado@example.com',
            'estado' => 'activo',
        ]);
    }

    #[Test]
    public function not_allow_create_duplicate_email_supplier()
    {
        Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Proveedor Original',
            'identificacion' => '111111111',
            'correo' => 'mismo@example.com',
            'estado' => 'activo',
        ]);

        $this->expectException(QueryException::class);

        Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Proveedor Duplicado',
            'identificacion' => '222222222',
            'correo' => 'mismo@example.com', // Duplicado
            'estado' => 'activo',
        ]);
    }

    #[Test]
    public function edit_supplier_name()
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Nombre Original',
            'identificacion' => '321654987',
            'correo' => 'original@example.com',
            'estado' => 'activo',
        ]);

        $supplier->update(['nombre_razon_social' => 'Nombre Actualizado']);

        $this->assertEquals('Nombre Actualizado', $supplier->fresh()->nombre_razon_social);
    }

    #[Test]
    public function edit_supplier_email()
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Test',
            'identificacion' => '147258369',
            'correo' => 'viejo@example.com',
            'estado' => 'activo',
        ]);

        $supplier->update(['correo' => 'NUEVO@EXAMPLE.COM']);

        $this->assertEquals('nuevo@example.com', $supplier->fresh()->correo);
    }

    #[Test]
    public function edit_supplier_type()
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Juan Pérez',
            'identificacion' => '951753852',
            'correo' => 'juan@example.com',
            'estado' => 'activo',
        ]);

        $supplier->update(['tipo_proveedor' => 'empresa']);

        $this->assertEquals('empresa', $supplier->fresh()->tipo_proveedor);
    }

    #[Test]
    public function edit_supplier_status()
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Test Estado',
            'identificacion' => '159357456',
            'correo' => 'estado@example.com',
            'estado' => 'activo',
        ]);

        $supplier->update(['estado' => 'inactivo']);

        $this->assertEquals('inactivo', $supplier->fresh()->estado);
    }

    #[Test]
    public function not_allow_duplicate_identification_supplier()
    {
        $supplier1 = Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Proveedor 1',
            'identificacion' => '111111111',
            'correo' => 'proveedor1@example.com',
            'estado' => 'activo',
        ]);

        $supplier2 = Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Proveedor 2',
            'identificacion' => '222222222',
            'correo' => 'proveedor2@example.com',
            'estado' => 'activo',
        ]);

        $this->expectException(QueryException::class);

        $supplier2->update(['identificacion' => '111111111']); // Ya existe
    }

    #[Test]
    public function not_allow_duplicate_email_supplier()
    {
        $supplier1 = Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Proveedor 1',
            'identificacion' => '333333333',
            'correo' => 'unico@example.com',
            'estado' => 'activo',
        ]);

        $supplier2 = Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Proveedor 2',
            'identificacion' => '444444444',
            'correo' => 'otro@example.com',
            'estado' => 'activo',
        ]);

        $this->expectException(QueryException::class);

        $supplier2->update(['correo' => 'unico@example.com']); // Ya existe
    }

    #[Test]
    public function add_customers_in_edit()
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'empresa',
            'nombre_razon_social' => 'Test',
            'identificacion' => '555555555',
            'correo' => 'test@example.com',
            'estado' => 'activo',
        ]);

        $customer = Customer::create([
            'name' => 'Cliente',
            'first_last_name' => 'Test',
            'id_type' => 'identification',
            'identification' => 'C999',
            'email' => 'cliente@test.com',
            'customer_type' => 'individual',
            'status' => true,
        ]);

        $supplier->customers()->attach($customer->id);

        $this->assertCount(1, $supplier->fresh()->customers);
        $this->assertTrue($supplier->customers->contains($customer));
    }

    #[Test]
    public function delete_customers_in_edit()
    {
        $supplier = Supplier::create([
            'tipo_proveedor' => 'empresa',
            'nombre_razon_social' => 'Test',
            'identificacion' => '666666666',
            'correo' => 'test@example.com',
            'estado' => 'activo',
        ]);

        $customer = Customer::create([
            'name' => 'Cliente',
            'first_last_name' => 'Test',
            'id_type' => 'identification',
            'identification' => 'C888',
            'email' => 'cliente888@test.com',
            'customer_type' => 'individual',
            'status' => true,
        ]);

        $supplier->customers()->attach($customer->id);
        $this->assertCount(1, $supplier->customers);

        $supplier->customers()->detach($customer->id);

        $this->assertCount(0, $supplier->fresh()->customers);
    }
}