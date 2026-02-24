<?php

namespace App\Http\Controllers;

use App\Services\EstadoFinancieroService;
use App\Exports\BalanceGeneralPDF;
use App\Exports\TrialBalancePDF;
use App\Exports\CashFlowStatementPDF;
use App\Exports\StatementOfChangesInEquityPDF;
use App\Exports\StatementOfComprehensiveIncomePDF;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EstadosFinancierosController extends Controller
{
    protected EstadoFinancieroService $estadoService;

    public function __construct(EstadoFinancieroService $estadoService)
    {
        $this->estadoService = $estadoService;
    }
     private function configurarServicio(Request $request, int $customerId): EstadoFinancieroService
    {
        $cliente = Customer::findOrFail($customerId);
        $tasa = $request->input('tasa_impuestos', 0);
        $servicio = $this->estadoService
            ->setCliente($cliente)
            ->setTasaImpuestos((float) $tasa);
        return $servicio;
    }
public function statementOfComprehensiveIncomePDF(Request $request, int $customerId)
{
    $fechaInicio = $request->query('fecha_inicio');
    $fechaFin    = $request->query('fecha_fin');

    $data = $this
        ->configurarServicio($request, $customerId)
        ->setFechas($fechaInicio, $fechaFin)
        ->estadoResultadosIntegral();

    $cliente = Customer::findOrFail($customerId);

    return (new StatementOfComprehensiveIncomePDF(
        $data,
        $fechaInicio,
        $fechaFin,
        $cliente
    ))->stream();
}
   
   public function balanceGeneral(Request $request, int $customerId)
{
    try {

        $fechaInicio   = $request->query('fecha_inicio');
        $fechaFin      = $request->query('fecha_fin');
        $balance = $this
            ->configurarServicio($request, $customerId)
            ->setFechas($fechaInicio, $fechaFin)
            ->setTasaImpuestos((float) $tasaImpuestos)
            ->balanceGeneral();

        return response()->json([
            'success' => true,
            'data' => $balance,
            'message' => 'Balance General generado correctamente',
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 400);
    }
}
public function balanceGeneralPDF(Request $request, int $customerId)
{
    try {

        $fechaInicio   = $request->query('fecha_inicio');
        $fechaFin      = $request->query('fecha_fin');
        $data = $this
            ->configurarServicio($request, $customerId)
            ->setFechas($fechaInicio, $fechaFin)
            ->balanceGeneral();
       $cliente = Customer::findOrFail($customerId);
        return (new BalanceGeneralPDF(
        $data,
        $request->input('fecha_inicio'),
        $request->input('fecha_fin'),
        $cliente
    ))->stream();

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 400);
    }
}
   
    public function estadoResultados(Request $request, int $customerId)
    {
        try {
            $estado = $this
                ->configurarServicio($request, $customerId)
                ->estadoResultados();

            return response()->json([
                'success' => true,
                'data' => $estado,
                'message' => 'Estado de Resultados generado correctamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    
    public function balanceComprobacionPDF(Request $request, int $customerId)
{
    $fechaInicio = $request->query('fecha_inicio');
    $fechaFin    = $request->query('fecha_fin');

    $data = $this
        ->configurarServicio($request, $customerId)
        ->setFechas($fechaInicio, $fechaFin)
        ->balanceComprobacion();

    $cliente = Customer::findOrFail($customerId);

    return (new TrialBalancePDF(
        $data,
        $fechaInicio,
        $fechaFin,
        $cliente
    ))->stream();
}

   public function flujoEfectivoPDF(Request $request, int $customerId)
{
    $fechaInicio = $request->query('fecha_inicio');
    $fechaFin    = $request->query('fecha_fin');

    $data = $this
        ->configurarServicio($request, $customerId)
        ->setFechas($fechaInicio, $fechaFin)
        ->flujoEfectivo();

    $cliente = Customer::findOrFail($customerId);

    return (new CashFlowStatementPDF(
        $data,
        $fechaInicio,
        $fechaFin,
        $cliente
    ))->stream();
}
   
    public function ratiosFinancieros(Request $request, int $customerId)
    {
        try {
            $ratios = $this
                ->configurarServicio($request, $customerId)
                ->ratiosFinancieros();

            return response()->json([
                'success' => true,
                'data' => $ratios,
                'message' => 'Ratios Financieros generados correctamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function cambiosPatrimonioPDF(Request $request, int $customerId)
{
    $fechaInicio = $request->query('fecha_inicio');
    $fechaFin    = $request->query('fecha_fin');

    $data = $this
        ->configurarServicio($request, $customerId)
        ->setFechas($fechaInicio, $fechaFin)
        ->cambiosPatrimonio();

    $cliente = Customer::findOrFail($customerId);

    return (new StatementOfChangesInEquityPDF(
        $data,
        $fechaInicio,
        $fechaFin,
        $cliente
    ))->stream();
}

    
    public function reporteCompleto(Request $request, int $customerId)
    {
        try {
            $servicio = $this->configurarServicio($request, $customerId);

            $datos = [
                'balance_general' => $servicio->balanceGeneral(),
                'estado_resultados' => $servicio->estadoResultados(),
                'balance_comprobacion' => $servicio->balanceComprobacion(),
                'flujo_efectivo' => $servicio->flujoEfectivo(),
                'ratios_financieros' => $servicio->ratiosFinancieros(),
                'cambios_patrimonio' => $servicio->cambiosPatrimonio(),
            ];

            return response()->json([
                'success' => true,
                'data' => $datos,
                'message' => 'Reporte completo generado correctamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}