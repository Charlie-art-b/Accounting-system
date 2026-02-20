<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\AccountingAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Services\EstadoFinancieroService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstadoFinancieroServiceTest extends TestCase
{
    use RefreshDatabase;

    protected   $service;
    protected Customer $cliente;
    protected array $cuentas = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = resolve(EstadoFinancieroService::class);
        $this->cliente = Customer::factory()->create();
        
        // Crear plan de cuentas
        $this->crearPlanCuentas();
        // Crear transacciones de ejemplo
        $this->crearTransacciones();
    }

    /**
     * Crea un plan de cuentas para pruebas
     */
    private function crearPlanCuentas(): void
    {
        $this->cuentas = [
            // Activos
            'caja' => AccountingAccount::create([
                'customer_id' => $this->cliente->id,
                'code' => '1100',
                'name' => 'Caja',
                'type' => 'Activo',
                'normal_balance' => 'debit',
                'status' => 'Activa',
            ]),
            'bancos' => AccountingAccount::create([
                'customer_id' => $this->cliente->id,
                'code' => '1110',
                'name' => 'Bancos',
                'type' => 'Activo',
                'normal_balance' => 'debit',
                'status' => 'Activa',
            ]),
            'cuentas_cobrar' => AccountingAccount::create([
                'customer_id' => $this->cliente->id,
                'code' => '1200',
                'name' => 'Cuentas por Cobrar',
                'type' => 'Activo',
                'normal_balance' => 'debit',
                'status' => 'Activa',
            ]),
            'inventario' => AccountingAccount::create([
                'customer_id' => $this->cliente->id,
                'code' => '1300',
                'name' => 'Inventario',
                'type' => 'Activo',
                'normal_balance' => 'debit',
                'status' => 'Activa',
            ]),
            // Pasivos
            'cuentas_pagar' => AccountingAccount::create([
                'customer_id' => $this->cliente->id,
                'code' => '2100',
                'name' => 'Cuentas por Pagar',
                'type' => 'Pasivo',
                'normal_balance' => 'credit',
                'status' => 'Activa',
            ]),
            'deuda_corto_plazo' => AccountingAccount::create([
                'customer_id' => $this->cliente->id,
                'code' => '2200',
                'name' => 'Deuda a Corto Plazo',
                'type' => 'Pasivo',
                'normal_balance' => 'credit',
                'status' => 'Activa',
            ]),
            // Patrimonio
            'capital' => AccountingAccount::create([
                'customer_id' => $this->cliente->id,
                'code' => '3100',
                'name' => 'Capital Social',
                'type' => 'Patrimonio',
                'normal_balance' => 'credit',
                'status' => 'Activa',
            ]),
            // Ingresos
            'ventas' => AccountingAccount::create([
                'customer_id' => $this->cliente->id,
                'code' => '4100',
                'name' => 'Ventas',
                'type' => 'Ingreso',
                'normal_balance' => 'credit',
                'status' => 'Activa',
            ]),
            'servicios' => AccountingAccount::create([
                'customer_id' => $this->cliente->id,
                'code' => '4200',
                'name' => 'Servicios Prestados',
                'type' => 'Ingreso',
                'normal_balance' => 'credit',
                'status' => 'Activa',
            ]),
            // Gastos
            'salarios' => AccountingAccount::create([
                'customer_id' => $this->cliente->id,
                'code' => '5100',
                'name' => 'Salarios',
                'type' => 'Gasto',
                'normal_balance' => 'debit',
                'status' => 'Activa',
            ]),
            'arrendamiento' => AccountingAccount::create([
                'customer_id' => $this->cliente->id,
                'code' => '5200',
                'name' => 'Arrendamiento',
                'type' => 'Gasto',
                'normal_balance' => 'debit',
                'status' => 'Activa',
            ]),
            'servicios_publicos' => AccountingAccount::create([
                'customer_id' => $this->cliente->id,
                'code' => '5300',
                'name' => 'Servicios Públicos',
                'type' => 'Gasto',
                'normal_balance' => 'debit',
                'status' => 'Activa',
            ]),
        ];
    }

    /**
     * Crea transacciones de ejemplo
     */
    private function crearTransacciones(): void
    {
        $fecha = Carbon::now()->subDays(20); // Crear transacciones en el pasado para que caigan en el rango de fechas

        // Asiento 1: Aporte de capital
        $asiento1 = JournalEntry::create([
            'customer_id' => $this->cliente->id,
            'journal_type' => 'general',
            'description' => 'Aporte de capital inicial',
            'reference' => 'APORTES-001',
            'total_debit' => 100000,
            'total_credit' => 100000,
            'posted_at' => $fecha->copy()->startOfMonth(),
            'posted_by' => null,
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento1->id,
            'accounting_account_id' => $this->cuentas['caja']->id,
            'description' => 'Aporte de capital',
            'debit' => 100000,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento1->id,
            'accounting_account_id' => $this->cuentas['capital']->id,
            'description' => 'Capital aportado',
            'debit' => 0,
            'credit' => 100000,
        ]);

        // Asiento 2: Ventas
        $asiento2 = JournalEntry::create([
            'customer_id' => $this->cliente->id,
            'journal_type' => 'sales',
            'description' => 'Venta de productos',
            'reference' => 'V-001',
            'total_debit' => 50000,
            'total_credit' => 50000,
            'posted_at' => $fecha->copy()->addDays(5),
            'posted_by' => null,
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento2->id,
            'accounting_account_id' => $this->cuentas['bancos']->id,
            'description' => 'Venta de productos',
            'debit' => 50000,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento2->id,
            'accounting_account_id' => $this->cuentas['ventas']->id,
            'description' => 'Ingreso por venta',
            'debit' => 0,
            'credit' => 50000,
        ]);

        // Asiento 3: Gastos de salarios
        $asiento3 = JournalEntry::create([
            'customer_id' => $this->cliente->id,
            'journal_type' => 'general',
            'description' => 'Pago de salarios',
            'reference' => 'GAS-001',
            'total_debit' => 15000,
            'total_credit' => 15000,
            'posted_at' => $fecha->copy()->addDays(10),
            'posted_by' => null,
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento3->id,
            'accounting_account_id' => $this->cuentas['salarios']->id,
            'description' => 'Gastos de salarios',
            'debit' => 15000,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento3->id,
            'accounting_account_id' => $this->cuentas['bancos']->id,
            'description' => 'Pago de salarios',
            'debit' => 0,
            'credit' => 15000,
        ]);

        // Asiento 4: Otros gastos
        $asiento4 = JournalEntry::create([
            'customer_id' => $this->cliente->id,
            'journal_type' => 'general',
            'description' => 'Gastos mensuales',
            'reference' => 'GAS-002',
            'total_debit' => 5000,
            'total_credit' => 5000,
            'posted_at' => $fecha->copy()->addDays(15),
            'posted_by' => null,
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento4->id,
            'accounting_account_id' => $this->cuentas['arrendamiento']->id,
            'description' => 'Arrendamiento oficina',
            'debit' => 2500,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento4->id,
            'accounting_account_id' => $this->cuentas['servicios_publicos']->id,
            'description' => 'Servicios públicos',
            'debit' => 2500,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $asiento4->id,
            'accounting_account_id' => $this->cuentas['bancos']->id,
            'description' => 'Pago de gastos',
            'debit' => 0,
            'credit' => 5000,
        ]);
    }

    /** @test */
    public function puede_generar_balance_general()
    {
        $balance = $this->service
            ->setCliente($this->cliente->id)
            ->setFechas(Carbon::now()->startOfYear(), Carbon::now())
            ->balanceGeneral();

        $this->assertIsArray($balance);
        $this->assertArrayHasKey('activos', $balance);
        $this->assertArrayHasKey('pasivos', $balance);
        $this->assertArrayHasKey('patrimonio', $balance);
        $this->assertArrayHasKey('ecuacion_balanceada', $balance);
    }

    /** @test */
    public function balance_general_tiene_estructura_correcta()
    {
        $balance = $this->service
            ->setCliente($this->cliente->id)
            ->setFechas(Carbon::now()->startOfYear(), Carbon::now())
            ->balanceGeneral();

        // Verifica estructura de activos
        $this->assertArrayHasKey('activos_circulantes', $balance['activos']);
        $this->assertArrayHasKey('activos_no_circulantes', $balance['activos']);
        $this->assertArrayHasKey('total', $balance['activos']);

        // Verifica estructura de pasivos
        $this->assertArrayHasKey('pasivos_circulantes', $balance['pasivos']);
        $this->assertArrayHasKey('pasivos_no_circulantes', $balance['pasivos']);
        $this->assertArrayHasKey('total', $balance['pasivos']);
    }

    /** @test */
    public function puede_generar_estado_resultados()
    {
        $estado = $this->service
            ->setCliente($this->cliente->id)
            ->setFechas(Carbon::now()->startOfYear(), Carbon::now())
            ->estadoResultados();

        $this->assertIsArray($estado);
        $this->assertArrayHasKey('ingresos', $estado);
        $this->assertArrayHasKey('gastos', $estado);
        $this->assertArrayHasKey('utilidad_neta', $estado);
        $this->assertArrayHasKey('margen_neto', $estado);
    }

    /** @test */
    public function estado_resultados_calcula_correctamente()
    {
        $estado = $this->service
            ->setCliente($this->cliente->id)
            ->setFechas(Carbon::now()->startOfYear(), Carbon::now())
            ->setTasaImpuestos(0.25)
            ->estadoResultados();

        // Ingresos deben ser 50000
        $this->assertEquals(50000, $estado['ingresos']['total']);

        // Gastos deben ser 20000
        $this->assertEquals(20000, $estado['gastos']['total']);

        // Utilidad bruta debe ser 30000
        $this->assertEquals(30000, $estado['utilidad_bruta']);

        // Utilidad neta debe ser 22500 (30000 - 25% de impuestos = 22500)
        $this->assertEquals(22500, $estado['utilidad_neta']);
    }

    /** @test */
    public function puede_generar_balance_comprobacion()
    {
        $comprobacion = $this->service
            ->setCliente($this->cliente->id)
            ->setFechas(Carbon::now()->startOfYear(), Carbon::now())
            ->balanceComprobacion();

        $this->assertIsArray($comprobacion);
        $this->assertArrayHasKey('cuentas', $comprobacion);
        $this->assertArrayHasKey('total_debe', $comprobacion);
        $this->assertArrayHasKey('total_haber', $comprobacion);
        $this->assertArrayHasKey('balanceado', $comprobacion);
    }

    /** @test */
    public function balance_comprobacion_debe_estar_balanceado()
    {
        $comprobacion = $this->service
            ->setCliente($this->cliente->id)
            ->setFechas(Carbon::now()->startOfYear(), Carbon::now())
            ->balanceComprobacion();

        // El balance debe estar balanceado
        $this->assertTrue($comprobacion['balanceado']);
        $this->assertLessThan(0.01, abs($comprobacion['diferencia']));
    }

    /** @test */
    public function puede_generar_ratios_financieros()
    {
        $ratios = $this->service
            ->setCliente($this->cliente->id)
            ->setFechas(Carbon::now()->startOfYear(), Carbon::now())
            ->ratiosFinancieros();

        $this->assertIsArray($ratios);
        $this->assertArrayHasKey('liquidez', $ratios);
        $this->assertArrayHasKey('solvencia', $ratios);
        $this->assertArrayHasKey('rentabilidad', $ratios);
    }

    /** @test */
    public function ratios_contienen_valores_reales()
    {
        $ratios = $this->service
            ->setCliente($this->cliente->id)
            ->setFechas(Carbon::now()->startOfYear(), Carbon::now())
            ->ratiosFinancieros();

        // Verifica que haya valores numéricos
        $this->assertIsNumeric($ratios['rentabilidad']['margen_neto']);
        $this->assertIsNumeric($ratios['rentabilidad']['roe']);
        $this->assertIsNumeric($ratios['rentabilidad']['roa']);
    }

    /** @test */
    public function puede_cambiar_cliente()
    {
        $cliente2 = Customer::factory()->create();

        // Prueba con cliente 1
        $balance1 = $this->service
            ->setCliente($this->cliente->id)
            ->setFechas(Carbon::now()->startOfYear(), Carbon::now())
            ->balanceGeneral();

        // Cambia a cliente 2
        $balance2 = $this->service
            ->setCliente($cliente2->id)
            ->setFechas(Carbon::now()->startOfYear(), Carbon::now())
            ->balanceGeneral();

        $this->assertIsArray($balance1);
        $this->assertIsArray($balance2);
    }

    /** @test */
    public function puede_usar_fechas_comparativas()
    {
        $balance = $this->service
            ->setCliente($this->cliente->id)
            ->setFechas(Carbon::now()->startOfYear(), Carbon::now())
            ->setFechaComparativa(Carbon::now()->subYear())
            ->balanceGeneral();

        $this->assertIsArray($balance);
        $this->assertArrayHasKey('activos', $balance);
    }
}
