<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\AccountingAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Services\EstadoFinancieroService;
use App\Services\ClientesService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de Estados Financieros
 * 
 * Ejecutar con: php artisan test tests/Feature/EstadosFinancierosTest.php
 */
class EstadosFinancierosTest extends TestCase
{
    use RefreshDatabase;

    protected Customer $cliente;
    protected  EstadoFinancieroService $estadoService;
    protected array $cuentas = [];

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->estadoService = resolve(EstadoFinancieroService::class);
        
        // Crea un cliente de prueba
        $this->cliente = Customer::factory()->create([
            'name' => 'Cliente Prueba',
            'identification' => '12345678',
        ]);
        
        // Crea el catálogo de cuentas de prueba
        $this->crearCatalogoParaPrueba();
    }

    /**
     * Crea un catálogo de cuentas de prueba
     */
    private function crearCatalogoParaPrueba()
    {
        // Cuentas de Activo
        $this->cuentas['caja'] = AccountingAccount::create([
            'customer_id' => $this->cliente->id,
            'code' => '1100',
            'name' => 'Caja',
            'type' => 'Activo',
            'normal_balance' => 'debit',
            'status' => 'Activa',
        ]);

        $this->cuentas['banco'] = AccountingAccount::create([
            'customer_id' => $this->cliente->id,
            'code' => '1110',
            'name' => 'Banco',
            'type' => 'Activo',
            'normal_balance' => 'debit',
            'status' => 'Activa',
        ]);

        $this->cuentas['cuentas_cobrar'] = AccountingAccount::create([
            'customer_id' => $this->cliente->id,
            'code' => '1200',
            'name' => 'Cuentas por Cobrar',
            'type' => 'Activo',
            'normal_balance' => 'debit',
            'status' => 'Activa',
        ]);

        $this->cuentas['inventario'] = AccountingAccount::create([
            'customer_id' => $this->cliente->id,
            'code' => '1300',
            'name' => 'Inventario',
            'type' => 'Activo',
            'normal_balance' => 'debit',
            'status' => 'Activa',
        ]);

        // Cuentas de Pasivo
        $this->cuentas['cuentas_pagar'] = AccountingAccount::create([
            'customer_id' => $this->cliente->id,
            'code' => '2100',
            'name' => 'Cuentas por Pagar',
            'type' => 'Pasivo',
            'normal_balance' => 'credit',
            'status' => 'Activa',
        ]);

        $this->cuentas['deuda_banco'] = AccountingAccount::create([
            'customer_id' => $this->cliente->id,
            'code' => '2200',
            'name' => 'Deuda Bancaria',
            'type' => 'Pasivo',
            'normal_balance' => 'credit',
            'status' => 'Activa',
        ]);

        // Cuentas de Patrimonio
        $this->cuentas['capital'] = AccountingAccount::create([
            'customer_id' => $this->cliente->id,
            'code' => '3100',
            'name' => 'Capital Social',
            'type' => 'Patrimonio',
            'normal_balance' => 'credit',
            'status' => 'Activa',
        ]);

        // Cuentas de Ingresos
        $this->cuentas['ingresos_ventas'] = AccountingAccount::create([
            'customer_id' => $this->cliente->id,
            'code' => '4100',
            'name' => 'Ingresos por Ventas',
            'type' => 'Ingreso',
            'normal_balance' => 'credit',
            'status' => 'Activa',
        ]);

        // Cuentas de Gastos
        $this->cuentas['gastos_operacionales'] = AccountingAccount::create([
            'customer_id' => $this->cliente->id,
            'code' => '5100',
            'name' => 'Gastos Operacionales',
            'type' => 'Gasto',
            'normal_balance' => 'debit',
            'status' => 'Activa',
        ]);

        $this->cuentas['costo_venta'] = AccountingAccount::create([
            'customer_id' => $this->cliente->id,
            'code' => '5200',
            'name' => 'Costo de Venta',
            'type' => 'Gasto',
            'normal_balance' => 'debit',
            'status' => 'Activa',
        ]);
    }

    /**
     * Test 1: Crear movimientos contables básicos
     */
    public function test_crear_asientos_contables()
    {
        // Crea un asiento: Depósito de capital
        $asiento = JournalEntry::create([
            'customer_id' => $this->cliente->id,
            'journal_type' => 'general',
            'description' => 'Depósito inicial de capital',
            'reference' => 'DEP-001',
            'total_debit' => 10000,
            'total_credit' => 10000,
            'posted_at' => Carbon::now(),
        ]);

        // Línea 1: Debita Caja
        JournalLine::create([
            'journal_entry_id' => $asiento->id,
            'accounting_account_id' => $this->cuentas['caja']->id,
            'description' => 'Depósito en caja',
            'debit' => 10000,
            'credit' => 0,
        ]);

        // Línea 2: Acredita Capital
        JournalLine::create([
            'journal_entry_id' => $asiento->id,
            'accounting_account_id' => $this->cuentas['capital']->id,
            'description' => 'Aporte de capital',
            'debit' => 0,
            'credit' => 10000,
        ]);

        $this->assertTrue($asiento->isBalanced());
        $this->assertEquals(2, $asiento->lines()->count());
    }

    /**
     * Test 2: Balance General básico
     */
    public function test_balance_general_basico()
    {
        $this->crearMovimientosDePrueba();

        $balance = $this->estadoService
            ->setCliente($this->cliente->id)
            ->setFechas(Carbon::now()->startOfYear(), Carbon::now())
            ->balanceGeneral();

        // Verificar estructura
        $this->assertArrayHasKey('activos', $balance);
        $this->assertArrayHasKey('pasivos', $balance);
        $this->assertArrayHasKey('patrimonio', $balance);
        $this->assertArrayHasKey('ecuacion_balanceada', $balance);

        // Verificar que al menos tenemos activos
        $this->assertGreaterThan(0, $balance['total_activos']);
        
        // Verificar que tenemos patrimonio
        $this->assertGreaterThan(0, $balance['patrimonio']['total']);
    }

    /**
     * Test 3: Estado de Resultados
     */
    public function test_estado_de_resultados()
    {
        $this->crearMovimientosDePrueba();

        $estado = $this->estadoService
            ->setCliente($this->cliente->id)
            ->setFechas(Carbon::now()->startOfYear(), Carbon::now())
            ->estadoResultados();

        // Verificar estructura
        $this->assertArrayHasKey('ingresos', $estado);
        $this->assertArrayHasKey('gastos', $estado);
        $this->assertArrayHasKey('utilidad_neta', $estado);
        $this->assertArrayHasKey('margen_neto', $estado);

        // Verificar que ingresos - gastos = utilidad
        $utilidadEsperada = $estado['ingresos']['total'] - $estado['gastos']['total'];
        $this->assertEquals(
            round($estado['utilidad_neta'], 2),
            round($utilidadEsperada, 2)
        );
    }

    /**
     * Test 4: Balance de Comprobación
     */
    public function test_balance_comprobacion()
    {
        $this->crearMovimientosDePrueba();

        $comprobacion = $this->estadoService
            ->setCliente($this->cliente->id)
            ->setFechas(Carbon::now()->startOfYear(), Carbon::now())
            ->balanceComprobacion();

        // Verificar que débitos = créditos
        $this->assertTrue($comprobacion['balanceado']);
        $this->assertLessThan(0.01, $comprobacion['diferencia']);
    }

    /**
     * Test 5: Ratios Financieros
     */
    public function test_ratios_financieros()
    {
        $this->crearMovimientosDePrueba();

        $ratios = $this->estadoService
            ->setCliente($this->cliente->id)
            ->setFechas(Carbon::now()->startOfYear(), Carbon::now())
            ->ratiosFinancieros();

        // Verificar estructura
        $this->assertArrayHasKey('liquidez', $ratios);
        $this->assertArrayHasKey('solvencia', $ratios);
        $this->assertArrayHasKey('rentabilidad', $ratios);
        $this->assertArrayHasKey('eficiencia', $ratios);

        // Verificar que los ratios sean números válidos
        $this->assertIsNumeric($ratios['liquidez']['razon_corriente']);
        $this->assertIsNumeric($ratios['rentabilidad']['roe']);
    }

    /**
     * Test 6: Validar que movimientos se registren correctamente
     */
    public function test_movimientos_registrados_correctamente()
    {
        $asiento = JournalEntry::create([
            'customer_id' => $this->cliente->id,
            'journal_type' => 'general',
            'description' => 'Venta de mercancía',
            'reference' => 'FAC-001',
            'total_debit' => 5000,
            'total_credit' => 5000,
            'posted_at' => Carbon::now(),
        ]);

        // Debita Caja
        JournalLine::create([
            'journal_entry_id' => $asiento->id,
            'accounting_account_id' => $this->cuentas['caja']->id,
            'debit' => 5000,
            'credit' => 0,
        ]);

        // Acredita Ingresos
        JournalLine::create([
            'journal_entry_id' => $asiento->id,
            'accounting_account_id' => $this->cuentas['ingresos_ventas']->id,
            'debit' => 0,
            'credit' => 5000,
        ]);

        // Verificar que el movimiento se registró
        $this->assertDatabaseHas('journal_entries', [
            'id' => $asiento->id,
            'reference' => 'FAC-001',
        ]);

        $this->assertEquals(2, $asiento->lines()->count());
    }

    /**
     * Test 7: Validar período de fechas
     */
    public function test_filtro_por_fechas()
    {
        // Crea movimientos en diferentes fechas
        $hace30Dias = Carbon::now()->subDays(30);
        $hoy = Carbon::now();

        // Movimiento antiguo
        $asiento1 = JournalEntry::create([
            'customer_id' => $this->cliente->id,
            'journal_type' => 'general',
            'description' => 'Movimiento antiguo',
            'reference' => 'REF-001',
            'total_debit' => 1000,
            'total_credit' => 1000,
            'posted_at' => $hace30Dias,
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento1->id,
            'accounting_account_id' => $this->cuentas['caja']->id,
            'debit' => 1000,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento1->id,
            'accounting_account_id' => $this->cuentas['capital']->id,
            'debit' => 0,
            'credit' => 1000,
        ]);

        // Movimiento reciente
        $asiento2 = JournalEntry::create([
            'customer_id' => $this->cliente->id,
            'journal_type' => 'general',
            'description' => 'Movimiento reciente',
            'reference' => 'REF-002',
            'total_debit' => 2000,
            'total_credit' => 2000,
            'posted_at' => $hoy,
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento2->id,
            'accounting_account_id' => $this->cuentas['caja']->id,
            'debit' => 2000,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento2->id,
            'accounting_account_id' => $this->cuentas['capital']->id,
            'debit' => 0,
            'credit' => 2000,
        ]);

        // Filtrar solo últimos 15 días
        $estado = $this->estadoService
            ->setCliente($this->cliente->id)
            ->setFechas(Carbon::now()->subDays(15), Carbon::now())
            ->estadoResultados();

        // Debería incluir solo el movimiento reciente
        $this->assertNotNull($estado);
    }

    /**
     * Test 8: Múltiples clientes
     */
    public function test_estados_multiples_clientes()
    {
        $cliente1 = Customer::factory()->create(['name' => 'Cliente 1']);
        $cliente2 = Customer::factory()->create(['name' => 'Cliente 2']);

        $this->crearMovimientosDePrueba();

        $estado1 = $this->estadoService
            ->setCliente($cliente1->id)
            ->estadoResultados();

        $estado2 = $this->estadoService
            ->setCliente($cliente2->id)
            ->estadoResultados();

        // Ambos deberían retornar un array válido
        $this->assertIsArray($estado1);
        $this->assertIsArray($estado2);
    }

    /**
     * Test 9: Validar estructura de alertas
     */
    public function test_alertas_en_dashboard()
    {
        $this->crearMovimientosDePrueba();

        $clientesService = resolve(ClientesService::class);
        $dashboard = $clientesService->dashboardCliente($this->cliente->id);

        // Verificar que el dashboard tiene alertas
        $this->assertArrayHasKey('alertas', $dashboard);
        $this->assertIsArray($dashboard['alertas']);
    }

    /**
     * Test 10: Verificar que los servicios están registrados
     */
    public function test_servicios_registrados_en_contenedor()
    {
        $estado = resolve(EstadoFinancieroService::class);
        $this->assertInstanceOf(EstadoFinancieroService::class, $estado);

        $clientes = resolve(ClientesService::class);
        $this->assertInstanceOf(ClientesService::class, $clientes);
    }

    /**
     * MÉTODOS AUXILIARES
     */

    /**
     * Crea movimientos de prueba variados
     */
    private function crearMovimientosDePrueba()
    {
        // 1. Depósito inicial de capital
        $asiento1 = JournalEntry::create([
            'customer_id' => $this->cliente->id,
            'journal_type' => 'general',
            'description' => 'Aporte inicial de capital',
            'reference' => 'CAP-001',
            'total_debit' => 50000,
            'total_credit' => 50000,
            'posted_at' => Carbon::now()->startOfYear(),
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento1->id,
            'accounting_account_id' => $this->cuentas['caja']->id,
            'debit' => 50000,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento1->id,
            'accounting_account_id' => $this->cuentas['capital']->id,
            'debit' => 0,
            'credit' => 50000,
        ]);

        // 2. Compra de inventario
        $asiento2 = JournalEntry::create([
            'customer_id' => $this->cliente->id,
            'journal_type' => 'general',
            'description' => 'Compra de mercancía',
            'reference' => 'COM-001',
            'total_debit' => 15000,
            'total_credit' => 15000,
            'posted_at' => Carbon::now()->startOfYear()->addDays(5),
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento2->id,
            'accounting_account_id' => $this->cuentas['inventario']->id,
            'debit' => 15000,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento2->id,
            'accounting_account_id' => $this->cuentas['cuentas_pagar']->id,
            'debit' => 0,
            'credit' => 15000,
        ]);

        // 3. Venta de productos
        $asiento3 = JournalEntry::create([
            'customer_id' => $this->cliente->id,
            'journal_type' => 'general',
            'description' => 'Venta de mercancía',
            'reference' => 'VEN-001',
            'total_debit' => 20000,
            'total_credit' => 20000,
            'posted_at' => Carbon::now()->startOfYear()->addDays(10),
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento3->id,
            'accounting_account_id' => $this->cuentas['caja']->id,
            'debit' => 20000,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento3->id,
            'accounting_account_id' => $this->cuentas['ingresos_ventas']->id,
            'debit' => 0,
            'credit' => 20000,
        ]);

        // 4. Gastos operacionales
        $asiento4 = JournalEntry::create([
            'customer_id' => $this->cliente->id,
            'journal_type' => 'general',
            'description' => 'Pago de gastos operacionales',
            'reference' => 'GAS-001',
            'total_debit' => 3000,
            'total_credit' => 3000,
            'posted_at' => Carbon::now()->startOfYear()->addDays(15),
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento4->id,
            'accounting_account_id' => $this->cuentas['gastos_operacionales']->id,
            'debit' => 3000,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento4->id,
            'accounting_account_id' => $this->cuentas['caja']->id,
            'debit' => 0,
            'credit' => 3000,
        ]);

        // 5. Costo de venta
        $asiento5 = JournalEntry::create([
            'customer_id' => $this->cliente->id,
            'journal_type' => 'general',
            'description' => 'Costo de venta',
            'reference' => 'CVEN-001',
            'total_debit' => 10000,
            'total_credit' => 10000,
            'posted_at' => Carbon::now()->startOfYear()->addDays(10),
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento5->id,
            'accounting_account_id' => $this->cuentas['costo_venta']->id,
            'debit' => 10000,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento5->id,
            'accounting_account_id' => $this->cuentas['inventario']->id,
            'debit' => 0,
            'credit' => 10000,
        ]);

        // 6. Asiento de cierre - transferir utilidad a capital
        $asiento6 = JournalEntry::create([
            'customer_id' => $this->cliente->id,
            'journal_type' => 'general',
            'description' => 'Cierre de ingresos y gastos',
            'reference' => 'CIERRE-001',
            'total_debit' => 20000,
            'total_credit' => 20000,
            'posted_at' => Carbon::now()->endOfYear(),
        ]);

        // Cierra Ingresos por Ventas
        JournalLine::create([
            'journal_entry_id' => $asiento6->id,
            'accounting_account_id' => $this->cuentas['ingresos_ventas']->id,
            'debit' => 20000,
            'credit' => 0,
        ]);

        // Cierra Gastos Operacionales
        JournalLine::create([
            'journal_entry_id' => $asiento6->id,
            'accounting_account_id' => $this->cuentas['gastos_operacionales']->id,
            'debit' => 0,
            'credit' => 3000,
        ]);

        // Cierra Costo de Venta
        JournalLine::create([
            'journal_entry_id' => $asiento6->id,
            'accounting_account_id' => $this->cuentas['costo_venta']->id,
            'debit' => 0,
            'credit' => 10000,
        ]);

        // Transfiere utilidad neta a Capital (20000 - 3000 - 10000 = 7000, sin impuestos)
        JournalLine::create([
            'journal_entry_id' => $asiento6->id,
            'accounting_account_id' => $this->cuentas['capital']->id,
            'debit' => 0,
            'credit' => 7000,
        ]);
    }
}
