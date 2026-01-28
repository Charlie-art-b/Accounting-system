<?php

namespace Tests\Unit\Models;

use App\Models\Customer;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test: Crear un cliente con datos válidos
     */
    public function test_crear_customer_con_datos_validos()
    {
        $datos = [
            'name' => 'Carlos',
            'first_last_name' => 'García',
            'second_last_name' => 'López',
            'id_type' => 'identification',
            'identification' => '123456789',
            'email' => 'carlos@example.com',
            'phone' => '88776655',
            'address' => 'Calle Principal 123',
            'customer_type' => 'individual',
            'status' => true,
            'notes' => 'Cliente especial',
        ];

        $customer = Customer::create($datos);

        $this->assertNotNull($customer->id);
        $this->assertEquals('Carlos', $customer->name);
        $this->assertEquals('García', $customer->first_last_name);
        $this->assertEquals('López', $customer->second_last_name);
        $this->assertEquals('identification', $customer->id_type);
        $this->assertEquals('123456789', $customer->identification);
        $this->assertEquals('carlos@example.com', $customer->email);
        $this->assertEquals('88776655', $customer->phone);
        $this->assertEquals('Calle Principal 123', $customer->address);
        $this->assertEquals('individual', $customer->customer_type);
        $this->assertTrue($customer->status);
        $this->assertEquals('Cliente especial', $customer->notes);
    }

    /**
     * Test: El email se guarda en minúsculas
     */
    public function test_email_se_guarda_en_minusculas()
    {
        $datos = [
            'name' => 'Juan',
            'first_last_name' => 'Pérez',
            'id_type' => 'identification',
            'identification' => '987654321',
            'email' => 'JUAN@EXAMPLE.COM',
            'customer_type' => 'individual',
        ];

        $customer = Customer::create($datos);

        $this->assertEquals('juan@example.com', $customer->email);
    }

    /**
     * Test: El email se trimea (espacios en blanco)
     */
    public function test_email_se_trimea()
    {
        $datos = [
            'name' => 'María',
            'first_last_name' => 'Rodríguez',
            'id_type' => 'dimex',
            'identification' => '555666777',
            'email' => '  maria@example.com  ',
            'customer_type' => 'individual',
        ];

        $customer = Customer::create($datos);

        $this->assertEquals('maria@example.com', $customer->email);
    }

    /**
     * Test: Crear cliente con identificación dimex
     */
    public function test_crear_customer_con_id_type_dimex()
    {
        $datos = [
            'name' => 'Ana',
            'first_last_name' => 'González',
            'id_type' => 'dimex',
            'identification' => 'DIMEX123456',
            'email' => 'ana@example.com',
            'customer_type' => 'individual',
        ];

        $customer = Customer::create($datos);

        $this->assertEquals('dimex', $customer->id_type);
        $this->assertEquals('DIMEX123456', $customer->identification);
    }

    /**
     * Test: Crear cliente con identificación passport
     */
    public function test_crear_customer_con_id_type_passport()
    {
        $datos = [
            'name' => 'Pedro',
            'first_last_name' => 'Sánchez',
            'id_type' => 'passport',
            'identification' => 'PASS789012',
            'email' => 'pedro@example.com',
            'customer_type' => 'individual',
        ];

        $customer = Customer::create($datos);

        $this->assertEquals('passport', $customer->id_type);
        $this->assertEquals('PASS789012', $customer->identification);
    }

    /**
     * Test: Crear cliente persona jurídica
     */
    public function test_crear_customer_tipo_persona_juridica()
    {
        $datos = [
            'name' => 'Empresa S.A.',
            'first_last_name' => 'Empresa',
            'id_type' => 'identification',
            'identification' => '3101234567',
            'email' => 'empresa@example.com',
            'customer_type' => 'legal_person',
        ];

        $customer = Customer::create($datos);

        $this->assertEquals('legal_person', $customer->customer_type);
    }

    /**
     * Test: El estado por defecto es activo (true) cuando se obtiene de la BD
     */
    public function test_estado_por_defecto_es_activo()
    {
        $customer = Customer::create([
            'name' => 'Luis',
            'first_last_name' => 'Fernández',
            'id_type' => 'identification',
            'identification' => '111222333',
            'email' => 'luis@example.com',
        ]);

        // Refrescar desde la BD para obtener el valor por defecto
        $customer->refresh();

        $this->assertTrue($customer->status);
    }

    /**
     * Test: Crear cliente con estado inactivo
     */
    public function test_crear_customer_inactivo()
    {
        $customer = Customer::create([
            'name' => 'Sofia',
            'first_last_name' => 'Torres',
            'id_type' => 'identification',
            'identification' => '444555666',
            'email' => 'sofia@example.com',
            'status' => false,
        ]);

        $this->assertFalse($customer->status);
    }

    /**
     * Test: Editar nombre del cliente
     */
    public function test_editar_nombre_cliente()
    {
        $customer = Customer::create([
            'name' => 'Miguel',
            'first_last_name' => 'López',
            'id_type' => 'identification',
            'identification' => '777888999',
            'email' => 'miguel@example.com',
        ]);

        $customer->update(['name' => 'Miguel Ángel']);

        $this->assertEquals('Miguel Ángel', $customer->name);
    }

    /**
     * Test: Editar apellidos del cliente
     */
    public function test_editar_apellidos_cliente()
    {
        $customer = Customer::create([
            'name' => 'Fernando',
            'first_last_name' => 'García',
            'second_last_name' => 'Martínez',
            'id_type' => 'identification',
            'identification' => '222333444',
            'email' => 'fernando@example.com',
        ]);

        $customer->update([
            'first_last_name' => 'Rodríguez',
            'second_last_name' => 'López',
        ]);

        $this->assertEquals('Rodríguez', $customer->first_last_name);
        $this->assertEquals('López', $customer->second_last_name);
    }

    /**
     * Test: Editar email del cliente (se aplican transformaciones)
     */
    public function test_editar_email_cliente()
    {
        $customer = Customer::create([
            'name' => 'Laura',
            'first_last_name' => 'Jiménez',
            'id_type' => 'identification',
            'identification' => '555666777888',
            'email' => 'laura@example.com',
        ]);

        $customer->update(['email' => '  LAURA.NUEVA@EXAMPLE.COM  ']);

        $this->assertEquals('laura.nueva@example.com', $customer->email);
    }

    /**
     * Test: Editar teléfono del cliente
     */
    public function test_editar_telefono_cliente()
    {
        $customer = Customer::create([
            'name' => 'Diego',
            'first_last_name' => 'Moreno',
            'id_type' => 'identification',
            'identification' => '999888777666',
            'email' => 'diego@example.com',
            'phone' => '87654321',
        ]);

        $customer->update(['phone' => '88998899']);

        $this->assertEquals('88998899', $customer->phone);
    }

    /**
     * Test: Editar dirección del cliente
     */
    public function test_editar_direccion_cliente()
    {
        $customer = Customer::create([
            'name' => 'Patricia',
            'first_last_name' => 'Valverde',
            'id_type' => 'identification',
            'identification' => '111333555777',
            'email' => 'patricia@example.com',
            'address' => 'Calle Vieja 100',
        ]);

        $customer->update(['address' => 'Calle Nueva 200']);

        $this->assertEquals('Calle Nueva 200', $customer->address);
    }

    /**
     * Test: Editar notas del cliente
     */
    public function test_editar_notas_cliente()
    {
        $customer = Customer::create([
            'name' => 'Ricardo',
            'first_last_name' => 'Cabrera',
            'id_type' => 'identification',
            'identification' => '222444666888',
            'email' => 'ricardo@example.com',
            'notes' => 'Cliente regular',
        ]);

        $customer->update(['notes' => 'Cliente VIP - Descuento especial 10%']);

        $this->assertEquals('Cliente VIP - Descuento especial 10%', $customer->notes);
    }

    /**
     * Test: Editar estado del cliente de activo a inactivo
     */
    public function test_editar_estado_activo_a_inactivo()
    {
        $customer = Customer::create([
            'name' => 'Valentina',
            'first_last_name' => 'Salazar',
            'id_type' => 'identification',
            'identification' => '333555777999',
            'email' => 'valentina@example.com',
            'status' => true,
        ]);

        $this->assertTrue($customer->status);

        $customer->update(['status' => false]);

        $this->assertFalse($customer->status);
        $this->assertFalse($customer->refresh()->status);
    }

    /**
     * Test: Editar estado del cliente de inactivo a activo
     */
    public function test_editar_estado_inactivo_a_activo()
    {
        $customer = Customer::create([
            'name' => 'Raúl',
            'first_last_name' => 'Valenzuela',
            'id_type' => 'identification',
            'identification' => '444666888111',
            'email' => 'raul@example.com',
            'status' => false,
        ]);

        $this->assertFalse($customer->status);

        $customer->update(['status' => true]);

        $this->assertTrue($customer->status);
        $this->assertTrue($customer->refresh()->status);
    }

    /**
     * Test: Editar tipo de cliente de individual a persona jurídica
     */
    public function test_editar_tipo_cliente()
    {
        $customer = Customer::create([
            'name' => 'Consultora XYZ',
            'first_last_name' => 'Consultora',
            'id_type' => 'identification',
            'identification' => '555777999111',
            'email' => 'consultora@example.com',
            'customer_type' => 'individual',
        ]);

        $this->assertEquals('individual', $customer->customer_type);

        $customer->update(['customer_type' => 'legal_person']);

        $this->assertEquals('legal_person', $customer->customer_type);
        $this->assertEquals('legal_person', $customer->refresh()->customer_type);
    }

    /**
     * Test: Editar múltiples campos a la vez
     */
    public function test_editar_multiples_campos()
    {
        $customer = Customer::create([
            'name' => 'Gabriel',
            'first_last_name' => 'Soto',
            'second_last_name' => 'Arias',
            'id_type' => 'identification',
            'identification' => '666888111222',
            'email' => 'gabriel@example.com',
            'phone' => '87123456',
            'address' => 'Avenida Central 50',
            'customer_type' => 'individual',
            'status' => true,
            'notes' => 'Sin notas',
        ]);

        $customer->update([
            'name' => 'Gabriel Andrés',
            'first_last_name' => 'Rodríguez',
            'phone' => '89654321',
            'address' => 'Avenida Norte 75',
            'status' => false,
            'notes' => 'Cliente con restricciones',
        ]);

        $this->assertEquals('Gabriel Andrés', $customer->name);
        $this->assertEquals('Rodríguez', $customer->first_last_name);
        $this->assertEquals('89654321', $customer->phone);
        $this->assertEquals('Avenida Norte 75', $customer->address);
        $this->assertFalse($customer->status);
        $this->assertEquals('Cliente con restricciones', $customer->notes);
    }

    /**
     * Test: El identificador único no permite duplicados
     */
    public function test_identificacion_debe_ser_unica()
    {
        Customer::create([
            'name' => 'Elena',
            'first_last_name' => 'Murillo',
            'id_type' => 'identification',
            'identification' => '777999111333',
            'email' => 'elena@example.com',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Customer::create([
            'name' => 'Elena Otra',
            'first_last_name' => 'Murillo',
            'id_type' => 'identification',
            'identification' => '777999111333', // Identificación duplicada
            'email' => 'elenaotra@example.com',
        ]);
    }

    /**
     * Test: El email debe ser único
     */
    public function test_email_debe_ser_unico()
    {
        Customer::create([
            'name' => 'Marcelo',
            'first_last_name' => 'Díaz',
            'id_type' => 'identification',
            'identification' => '888111222333',
            'email' => 'marcelo@example.com',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Customer::create([
            'name' => 'Marcelo Otro',
            'first_last_name' => 'García',
            'id_type' => 'identification',
            'identification' => '888111222444',
            'email' => 'marcelo@example.com', // Email duplicado
        ]);
    }

    /**
     * Test: Verificar que los campos fillable están definidos correctamente
     */
    public function test_fillable_fields_configurados()
    {
        $customer = new Customer();

        $fillable = $customer->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('first_last_name', $fillable);
        $this->assertContains('second_last_name', $fillable);
        $this->assertContains('id_type', $fillable);
        $this->assertContains('identification', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('phone', $fillable);
        $this->assertContains('address', $fillable);
        $this->assertContains('customer_type', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('notes', $fillable);
    }

    /**
     * Test: Verificar que los tipos de datos (casts) están correctamente configurados
     */
    public function test_casts_configurados()
    {
        $customer = Customer::create([
            'name' => 'Sebastián',
            'first_last_name' => 'Bravo',
            'id_type' => 'identification',
            'identification' => '999222333444',
            'email' => 'sebastian@example.com',
            'status' => 1,
            'customer_type' => 'individual',
        ]);

        // El status debe ser booleano
        $this->assertIsBool($customer->status);
        $this->assertTrue($customer->status);

        // El customer_type debe ser string
        $this->assertIsString($customer->customer_type);
        $this->assertEquals('individual', $customer->customer_type);
    }

    /**
     * Test: Obtener cliente por ID
     */
    public function test_obtener_customer_por_id()
    {
        $customer = Customer::create([
            'name' => 'Alejandra',
            'first_last_name' => 'Navarro',
            'id_type' => 'identification',
            'identification' => '111444777999',
            'email' => 'alejandra@example.com',
        ]);

        $retrieved = Customer::find($customer->id);

        $this->assertNotNull($retrieved);
        $this->assertEquals($customer->id, $retrieved->id);
        $this->assertEquals('Alejandra', $retrieved->name);
    }

    /**
     * Test: Actualizar y refrescar cliente desde la base de datos
     */
    public function test_actualizar_y_refrescar_customer()
    {
        $customer = Customer::create([
            'name' => 'Andrés',
            'first_last_name' => 'Campos',
            'id_type' => 'identification',
            'identification' => '222555888111',
            'email' => 'andres@example.com',
            'status' => true,
        ]);

        $customer->update(['status' => false]);
        $customer->refresh();

        $this->assertFalse($customer->status);

        // Verificar que el cambio se persiste en la BD
        $freshCustomer = Customer::find($customer->id);
        $this->assertFalse($freshCustomer->status);
    }

    /**
     * Test: Listar todos los clientes
     */
    public function test_listar_todos_los_clientes()
    {
        // Crear varios clientes
        Customer::create([
            'name' => 'Cliente 1',
            'first_last_name' => 'Apellido',
            'id_type' => 'identification',
            'identification' => '100100100100',
            'email' => 'cliente1@example.com',
        ]);

        Customer::create([
            'name' => 'Cliente 2',
            'first_last_name' => 'Apellido',
            'id_type' => 'identification',
            'identification' => '200200200200',
            'email' => 'cliente2@example.com',
        ]);

        Customer::create([
            'name' => 'Cliente 3',
            'first_last_name' => 'Apellido',
            'id_type' => 'identification',
            'identification' => '300300300300',
            'email' => 'cliente3@example.com',
        ]);

        // Obtener todos los clientes
        $customers = Customer::all();

        // Verificar que existen 3 clientes
        $this->assertCount(3, $customers);
        
        // Verificar que se pueden acceder a los datos de cada cliente
        $this->assertContains('Cliente 1', $customers->pluck('name'));
        $this->assertContains('Cliente 2', $customers->pluck('name'));
        $this->assertContains('Cliente 3', $customers->pluck('name'));
    }

    /**
     * Test: Listar clientes con paginación
     */
    public function test_listar_clientes_con_paginacion()
    {
        // Crear 10 clientes
        for ($i = 1; $i <= 10; $i++) {
            Customer::create([
                'name' => "Cliente {$i}",
                'first_last_name' => 'Apellido',
                'id_type' => 'identification',
                'identification' => str_pad($i, 12, '0', STR_PAD_LEFT),
                'email' => "cliente{$i}@example.com",
            ]);
        }

        // Obtener clientes con paginación de 5 por página
        $customers = Customer::paginate(5);

        $this->assertCount(5, $customers);
        $this->assertEquals(10, $customers->total());
        $this->assertEquals(1, $customers->currentPage());
    }

    /**
     * Test: Listar clientes activos
     */
    public function test_listar_clientes_activos()
    {
        // Crear clientes activos e inactivos
        Customer::create([
            'name' => 'Cliente Activo 1',
            'first_last_name' => 'Apellido',
            'id_type' => 'identification',
            'identification' => '111111111111',
            'email' => 'activo1@example.com',
            'status' => true,
        ]);

        Customer::create([
            'name' => 'Cliente Inactivo',
            'first_last_name' => 'Apellido',
            'id_type' => 'identification',
            'identification' => '222222222222',
            'email' => 'inactivo@example.com',
            'status' => false,
        ]);

        Customer::create([
            'name' => 'Cliente Activo 2',
            'first_last_name' => 'Apellido',
            'id_type' => 'identification',
            'identification' => '333333333333',
            'email' => 'activo2@example.com',
            'status' => true,
        ]);

        // Obtener solo clientes activos
        $activeCustomers = Customer::where('status', true)->get();

        $this->assertCount(2, $activeCustomers);
        $this->assertTrue($activeCustomers->every(fn($customer) => $customer->status === true));
    }

    /**
     * Test: Listar clientes inactivos
     */
    public function test_listar_clientes_inactivos()
    {
        Customer::create([
            'name' => 'Cliente Activo',
            'first_last_name' => 'Apellido',
            'id_type' => 'identification',
            'identification' => '444444444444',
            'email' => 'activo@example.com',
            'status' => true,
        ]);

        Customer::create([
            'name' => 'Cliente Inactivo 1',
            'first_last_name' => 'Apellido',
            'id_type' => 'identification',
            'identification' => '555555555555',
            'email' => 'inactivo1@example.com',
            'status' => false,
        ]);

        Customer::create([
            'name' => 'Cliente Inactivo 2',
            'first_last_name' => 'Apellido',
            'id_type' => 'identification',
            'identification' => '666666666666',
            'email' => 'inactivo2@example.com',
            'status' => false,
        ]);

        // Obtener solo clientes inactivos
        $inactiveCustomers = Customer::where('status', false)->get();

        $this->assertCount(2, $inactiveCustomers);
        $this->assertTrue($inactiveCustomers->every(fn($customer) => $customer->status === false));
    }

    /**
     * Test: Eliminar un cliente
     */
    public function test_eliminar_cliente()
    {
        $customer = Customer::create([
            'name' => 'Cliente a Eliminar',
            'first_last_name' => 'Apellido',
            'id_type' => 'identification',
            'identification' => '777777777777',
            'email' => 'eliminar@example.com',
        ]);

        $customerId = $customer->id;

        // Verificar que el cliente existe
        $this->assertNotNull(Customer::find($customerId));

        // Eliminar el cliente
        $customer->delete();

        // Verificar que el cliente fue eliminado
        $this->assertNull(Customer::find($customerId));
    }

    /**
     * Test: Eliminar cliente mediante ID
     */
    public function test_eliminar_cliente_por_id()
    {
        $customer = Customer::create([
            'name' => 'Cliente a Eliminar por ID',
            'first_last_name' => 'Apellido',
            'id_type' => 'identification',
            'identification' => '888888888888',
            'email' => 'eliminarid@example.com',
        ]);

        $customerId = $customer->id;

        // Eliminar el cliente mediante ID
        Customer::destroy($customerId);

        // Verificar que el cliente fue eliminado
        $this->assertNull(Customer::find($customerId));
    }

    /**
     * Test: Eliminar múltiples clientes a la vez
     */
    public function test_eliminar_multiples_clientes()
    {
        $customer1 = Customer::create([
            'name' => 'Cliente 1 Borrar',
            'first_last_name' => 'Apellido',
            'id_type' => 'identification',
            'identification' => '999999999999',
            'email' => 'borrar1@example.com',
        ]);

        $customer2 = Customer::create([
            'name' => 'Cliente 2 Borrar',
            'first_last_name' => 'Apellido',
            'id_type' => 'identification',
            'identification' => '101010101010',
            'email' => 'borrar2@example.com',
        ]);

        $customer3 = Customer::create([
            'name' => 'Cliente 3 Mantener',
            'first_last_name' => 'Apellido',
            'id_type' => 'identification',
            'identification' => '121212121212',
            'email' => 'mantener@example.com',
        ]);

        // Eliminar los primeros dos clientes
        Customer::destroy([$customer1->id, $customer2->id]);

        // Verificar que fueron eliminados
        $this->assertNull(Customer::find($customer1->id));
        $this->assertNull(Customer::find($customer2->id));

        // Verificar que el tercero sigue existiendo
        $this->assertNotNull(Customer::find($customer3->id));
    }

    /**
     * Test: Eliminar clientes con condición
     */
    public function test_eliminar_clientes_inactivos()
    {
        Customer::create([
            'name' => 'Cliente Activo Permanente',
            'first_last_name' => 'Apellido',
            'id_type' => 'identification',
            'identification' => '131313131313',
            'email' => 'activo@example.com',
            'status' => true,
        ]);

        Customer::create([
            'name' => 'Cliente Inactivo a Eliminar',
            'first_last_name' => 'Apellido',
            'id_type' => 'identification',
            'identification' => '141414141414',
            'email' => 'inactivo@example.com',
            'status' => false,
        ]);

        // Contar clientes antes de eliminar
        $countBefore = Customer::count();
        $this->assertEquals(2, $countBefore);

        // Eliminar solo los clientes inactivos
        Customer::where('status', false)->delete();

        // Verificar que solo queda el cliente activo
        $this->assertCount(1, Customer::all());
        $this->assertEquals('Cliente Activo Permanente', Customer::first()->name);
    }
}
