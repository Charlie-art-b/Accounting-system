<?php

namespace App\Http\Controllers;

use App\Services\EstadoFinancieroService;
use App\Exports\BalanceGeneralPDF;
use App\Exports\TrialBalancePDF;
use App\Exports\CashFlowStatementPDF;
use App\Exports\StatementOfChangesInEquityPDF;
use App\Exports\StatementOfComprehensiveIncomePDF;
use App\Exports\EstadoResultadosPDF;
use App\Exports\BalanceComprobacionPDF;
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
        $tasaImpuestos = $request->query('tasa_impuestos', 0);

        $data = $this
            ->configurarServicio($request, $customerId)
            ->setFechas($fechaInicio, $fechaFin)
            ->setTasaImpuestos((float) $tasaImpuestos)
            ->estadoResultadosIntegral();

        $cliente = Customer::findOrFail($customerId);

        $fileName = "Estado_Resultados_Integral_{$cliente->id}_{$fechaInicio}_{$fechaFin}.pdf";

        return (new StatementOfComprehensiveIncomePDF(
            $data,
            $fechaInicio,
            $fechaFin,
            $cliente
        ))->download($fileName);
    }
    
    public function balanceGeneral(Request $request, int $customerId)
    {
        try {

            $fechaInicio   = $request->query('fecha_inicio');
            $fechaFin      = $request->query('fecha_fin');
            $tasaImpuestos = $request->input('tasa_impuestos', 0);

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
            $fechaInicio = $request->query('fecha_inicio');
            $fechaFin    = $request->query('fecha_fin');
            $tasaImpuestos = $request->query('tasa_impuestos', 0);

            $data = $this
                ->configurarServicio($request, $customerId)
                ->setFechas($fechaInicio, $fechaFin)
                ->setTasaImpuestos((float) $tasaImpuestos)
                ->balanceGeneral();

            $cliente = Customer::findOrFail($customerId);

            $fileName = "Balance_General_{$cliente->id}_{$fechaInicio}_{$fechaFin}.pdf";

            return (new BalanceGeneralPDF(
                $data,
                $fechaInicio,
                $fechaFin,
                $cliente
            ))->download($fileName);

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
            $fechaInicio   = $request->query('fecha_inicio');
            $fechaFin      = $request->query('fecha_fin');

            $estado = $this
                ->configurarServicio($request, $customerId)
                ->setFechas($fechaInicio, $fechaFin)
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

    public function estadoResultadosPDF(Request $request, int $customerId)
    {
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin    = $request->query('fecha_fin');
        $tasaImpuestos = $request->query('tasa_impuestos', 0);

        $data = $this
            ->configurarServicio($request, $customerId)
            ->setFechas($fechaInicio, $fechaFin)
            ->setTasaImpuestos((float) $tasaImpuestos)
            ->estadoResultados();

        $cliente = Customer::findOrFail($customerId);

        $fileName = "Estado_Resultados_{$cliente->id}_{$fechaInicio}_{$fechaFin}.pdf";

        return (new EstadoResultadosPDF(
            $data,
            $fechaInicio,
            $fechaFin,
            $cliente
        ))->download($fileName);
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

        $fileName = "Balance_Comprobacion_{$cliente->id}_{$fechaInicio}_{$fechaFin}.pdf";

        return (new TrialBalancePDF($data, $fechaInicio, $fechaFin, $cliente))
            ->download($fileName);
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

        $fileName = "Flujo_Efectivo_{$cliente->id}_{$fechaInicio}_{$fechaFin}.pdf";

        return (new CashFlowStatementPDF($data, $fechaInicio, $fechaFin, $cliente))
            ->download($fileName);
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

        $fileName = "Cambios_Patrimonio_{$cliente->id}_{$fechaInicio}_{$fechaFin}.pdf";

        return (new StatementOfChangesInEquityPDF($data, $fechaInicio, $fechaFin, $cliente))
            ->download($fileName);
    }

    
    public function reporteCompleto(Request $request, int $customerId)
    {
        try {
            $fechaInicio = $request->query('fecha_inicio');
            $fechaFin    = $request->query('fecha_fin');

            $servicio = $this
            ->configurarServicio($request, $customerId)
            ->setFechas($fechaInicio, $fechaFin);
            
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