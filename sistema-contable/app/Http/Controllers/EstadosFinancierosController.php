<?php

namespace App\Http\Controllers;

use App\Services\EstadoFinancieroService;
use App\Services\AnalisisComparativoService;
use App\Services\AnalisisDeudoresService;
use App\Services\AnalisisAcreedoresService;
use App\Services\AnalisisInventarioService;
use App\Services\ClientesService;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Controlador de Estados Financieros
 * 
 * Ejemplo de integración de todos los servicios de estados financieros
 * para una aplicación web o API.
 */
class EstadosFinancierosController extends Controller
{
    protected EstadoFinancieroService $estadoService;
    protected AnalisisComparativoService $comparativoService;
    protected AnalisisDeudoresService $deudoresService;
    protected AnalisisAcreedoresService $acreedoresService;
    protected AnalisisInventarioService $inventarioService;
    protected ClientesService $clientesService;

    public function __construct(
        EstadoFinancieroService $estadoService,
        AnalisisComparativoService $comparativoService,
        AnalisisDeudoresService $deudoresService,
        AnalisisAcreedoresService $acreedoresService,
        AnalisisInventarioService $inventarioService,
        ClientesService $clientesService
    ) {
        $this->estadoService = $estadoService;
        $this->comparativoService = $comparativoService;
        $this->deudoresService = $deudoresService;
        $this->acreedoresService = $acreedoresService;
        $this->inventarioService = $inventarioService;
        $this->clientesService = $clientesService;
    }

    /**
     * Dashboard integral de un cliente
     * GET /estados-financieros/cliente/{id}
     */
    public function dashboardCliente($customerId)
    {
        $dashboard = $this->clientesService->dashboardCliente($customerId);
        return response()->json($dashboard);
    }

    /**
     * Balance General
     * GET /estados-financieros/{clienteId}/balance-general
     */
    public function balanceGeneral(Request $request, $customerId)
    {
        $inicio = $request->input('inicio', Carbon::now()->startOfYear());
        $fin = $request->input('fin', Carbon::now());

        $balance = $this->estadoService
            ->setCliente($customerId)
            ->setFechas($inicio, $fin)
            ->balanceGeneral();

        return response()->json($balance);
    }

    /**
     * Estado de Resultados
     * GET /estados-financieros/{clienteId}/estado-resultados
     */
    public function estadoResultados(Request $request, $customerId)
    {
        $inicio = $request->input('inicio', Carbon::now()->startOfYear());
        $fin = $request->input('fin', Carbon::now());

        $estado = $this->estadoService
            ->setCliente($customerId)
            ->setFechas($inicio, $fin)
            ->estadoResultados();

        return response()->json($estado);
    }

    /**
     * Balance de Comprobación
     * GET /estados-financieros/{clienteId}/balance-comprobacion
     */
    public function balanceComprobacion(Request $request, $customerId)
    {
        $inicio = $request->input('inicio', Carbon::now()->startOfYear());
        $fin = $request->input('fin', Carbon::now());

        $comprobacion = $this->estadoService
            ->setCliente($customerId)
            ->setFechas($inicio, $fin)
            ->balanceComprobacion();

        return response()->json($comprobacion);
    }

    /**
     * Flujo de Efectivo
     * GET /estados-financieros/{clienteId}/flujo-efectivo
     */
    public function flujoEfectivo(Request $request, $customerId)
    {
        $inicio = $request->input('inicio', Carbon::now()->startOfYear());
        $fin = $request->input('fin', Carbon::now());

        $flujo = $this->estadoService
            ->setCliente($customerId)
            ->setFechas($inicio, $fin)
            ->flujoEfectivo();

        return response()->json($flujo);
    }

    /**
     * Ratios Financieros
     * GET /estados-financieros/{clienteId}/ratios
     */
    public function ratiosFinancieros(Request $request, $customerId)
    {
        $inicio = $request->input('inicio', Carbon::now()->startOfYear());
        $fin = $request->input('fin', Carbon::now());

        $ratios = $this->estadoService
            ->setCliente($customerId)
            ->setFechas($inicio, $fin)
            ->ratiosFinancieros();

        return response()->json($ratios);
    }

    /**
     * Análisis Comparativo Horizontal
     * GET /estados-financieros/{clienteId}/analisis-horizontal
     */
    public function analisisHorizontal(Request $request, $customerId)
    {
        $periodoActual = $request->input('periodo_actual', Carbon::now());
        $periodoAnterior = $request->input('periodo_anterior', Carbon::now()->subYear());

        $horizontal = $this->comparativoService
            ->setCliente($customerId)
            ->setPeriodos($periodoActual, $periodoAnterior)
            ->analisisHorizontal();

        return response()->json($horizontal);
    }

    /**
     * Análisis Vertical
     * GET /estados-financieros/{clienteId}/analisis-vertical
     */
    public function analisisVertical(Request $request, $customerId)
    {
        $fecha = $request->input('fecha', Carbon::now());

        $vertical = $this->comparativoService
            ->setCliente($customerId)
            ->analisisVertical();

        return response()->json($vertical);
    }

    /**
     * Cuentas por Cobrar - Resumen
     * GET /estados-financieros/{clienteId}/cuentas-cobrar
     */
    public function cuentasPorCobrar($customerId)
    {
        $resumen = $this->deudoresService->resumenCuentasPorCobrar($customerId);
        return response()->json($resumen);
    }

    /**
     * Cuentas por Cobrar - Detalle Clasificado
     * GET /estados-financieros/{clienteId}/cuentas-cobrar/clasificado
     */
    public function cuentasPorCobrarClasificado($customerId)
    {
        $clasificado = $this->deudoresService->detalleClasificado($customerId);
        return response()->json($clasificado);
    }

    /**
     * Análisis por Cliente (Deudores)
     * GET /estados-financieros/{clienteId}/deudores-detalle
     */
    public function analisisDeudores($customerId)
    {
        $analisis = $this->deudoresService->analisisPorCliente($customerId);
        return response()->json($analisis);
    }

    /**
     * Indicadores de Cobranza
     * GET /estados-financieros/{clienteId}/indicadores-cobranza
     */
    public function indicadoresCobranza($customerId)
    {
        $indicadores = $this->deudoresService->indicadoresCobranza($customerId);
        return response()->json($indicadores);
    }

    /**
     * Cuentas por Pagar - Resumen
     * GET /estados-financieros/{clienteId}/cuentas-pagar
     */
    public function cuentasPorPagar($customerId)
    {
        $resumen = $this->acreedoresService->resumenCuentasPorPagar($customerId);
        return response()->json($resumen);
    }

    /**
     * Cuentas por Pagar - Detalle Clasificado
     * GET /estados-financieros/{clienteId}/cuentas-pagar/clasificado
     */
    public function cuentasPorPagarClasificado($customerId)
    {
        $clasificado = $this->acreedoresService->detalleClasificado($customerId);
        return response()->json($clasificado);
    }

    /**
     * Análisis por Proveedor
     * GET /estados-financieros/{clienteId}/proveedores-detalle
     */
    public function analisisAcreedores($customerId)
    {
        $analisis = $this->acreedoresService->analisisPorProveedor($customerId);
        return response()->json($analisis);
    }

    /**
     * Proyección de Flujo de Caja
     * GET /estados-financieros/{clienteId}/proyeccion-flujo
     */
    public function proyeccionFlujoCaja(Request $request, $customerId)
    {
        $dias = $request->input('dias', 90);

        $proyeccion = $this->acreedoresService->proyeccionFlujoCaja($customerId, $dias);
        return response()->json($proyeccion);
    }

    /**
     * Inventario - Resumen
     * GET /estados-financieros/{clienteId}/inventario
     */
    public function inventarioResumen($customerId)
    {
        $resumen = $this->inventarioService->resumenInventario($customerId);
        return response()->json($resumen);
    }

    /**
     * Análisis de Rotación
     * GET /estados-financieros/{clienteId}/rotacion-inventario
     */
    public function rotacionInventario($customerId)
    {
        $rotacion = $this->inventarioService->analisisRotacion($customerId);
        return response()->json($rotacion);
    }

    /**
     * Productos de Lento Movimiento
     * GET /estados-financieros/{clienteId}/inventario/lento-movimiento
     */
    public function productosLentoMovimiento($customerId)
    {
        $lento = $this->inventarioService->productosLentoMovimiento($customerId);
        return response()->json($lento);
    }

    /**
     * Análisis ABC
     * GET /estados-financieros/{clienteId}/inventario/analisis-abc
     */
    public function analisisABC($customerId)
    {
        $abc = $this->inventarioService->analisisABC($customerId);
        return response()->json($abc);
    }

    /**
     * Comparativa entre Clientes
     * GET /estados-financieros/comparativa
     * Query params: clientes[]=1&clientes[]=2&clientes[]=3
     */
    public function comparativaClientes(Request $request)
    {
        $customerIds = $request->input('clientes', []);

        if (empty($customerIds)) {
            return response()->json(['error' => 'Debe proporcionar IDs de clientes'], 400);
        }

        $comparativa = $this->clientesService->comparativaClientes($customerIds);
        return response()->json($comparativa);
    }

    /**
     * Listado de Clientes con Estado
     * GET /estados-financieros/clientes
     */
    public function listadoClientes()
    {
        $listado = $this->clientesService->listadoClientesConEstado();
        return response()->json($listado);
    }

    /**
     * Exportar Balance General a PDF
     * GET /estados-financieros/{clienteId}/balance-general/pdf
     */
    public function exportarBalancePDF($customerId)
    {
        $balance = $this->estadoService
            ->setCliente($customerId)
            ->balanceGeneral();

        // Implementar lógica de PDF con librería como DomPDF
        // return response()->download($pdf_path);
    }

    /**
     * Exportar Estado de Resultados a Excel
     * GET /estados-financieros/{clienteId}/estado-resultados/excel
     */
    public function exportarEstadoExcel($customerId)
    {
        $estado = $this->estadoService
            ->setCliente($customerId)
            ->estadoResultados();

        // Implementar lógica de Excel con librería como Maatwebsite/Excel
        // return response()->download($excel_path);
    }

    /**
     * Obtener Alertas
     * GET /estados-financieros/{clienteId}/alertas
     */
    public function obtenerAlertas(Request $request, $customerId)
    {
        $fecha = $request->input('fecha', Carbon::now());

        $dashboard = $this->clientesService->dashboardCliente($customerId, $fecha);
        $alertas = $dashboard['alertas'] ?? [];

        return response()->json([
            'cliente_id' => $customerId,
            'total_alertas' => count($alertas),
            'alertas' => $alertas,
        ]);
    }
}
