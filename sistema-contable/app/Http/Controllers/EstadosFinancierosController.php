<?php

namespace App\Http\Controllers;

use App\Services\EstadoFinancieroService;
use App\Exports\BalanceGeneralPDF;
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

    
    public function balanceComprobacion(Request $request, int $customerId)
    {
        try {
            $comprobacion = $this
                ->configurarServicio($request, $customerId)
                ->balanceComprobacion();

            return response()->json([
                'success' => true,
                'data' => $comprobacion,
                'message' => 'Balance de Comprobación generado correctamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * ✅ FLUJO DE EFECTIVO - JSON
     * GET /api/estados-financieros/{customerId}/flujo-efectivo
     */
    public function flujoEfectivo(Request $request, int $customerId)
    {
        try {
            $flujo = $this
                ->configurarServicio($request, $customerId)
                ->flujoEfectivo();

            return response()->json([
                'success' => true,
                'data' => $flujo,
                'message' => 'Flujo de Efectivo generado correctamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * ✅ RATIOS FINANCIEROS - JSON
     * GET /api/estados-financieros/{customerId}/ratios-financieros
     */
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

    /**
     * ✅ CAMBIOS EN PATRIMONIO - JSON
     * GET /api/estados-financieros/{customerId}/cambios-patrimonio
     */
    public function cambiosPatrimonio(Request $request, int $customerId)
    {
        try {
            $cambios = $this
                ->configurarServicio($request, $customerId)
                ->cambiosPatrimonio();

            return response()->json([
                'success' => true,
                'data' => $cambios,
                'message' => 'Cambios en Patrimonio generados correctamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * ✅ TODOS LOS ESTADOS FINANCIEROS - JSON
     * GET /api/estados-financieros/{customerId}/completo
     */
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