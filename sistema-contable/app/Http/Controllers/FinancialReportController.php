<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\FinancialReport;
use App\Services\EstadoFinancieroService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FinancialReportController extends Controller
{
    public function generate(Request $request, EstadoFinancieroService $service)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'tasa_impuestos' => ['nullable', 'numeric', 'min:0'],
            'report_type' => ['required', Rule::in([
                'balance_general',
                'estado_resultados',
                'balance_comprobacion',
                'flujo_efectivo',
                'cambios_patrimonio',
                'estado_resultados_integral',
            ])],
        ]);

        $cliente = Customer::findOrFail($data['customer_id']);

        $service
            ->setCliente($cliente)
            ->setFechas($data['fecha_inicio'], $data['fecha_fin'])
            ->setTasaImpuestos((float) ($data['tasa_impuestos'] ?? 0));

        $payload = match ($data['report_type']) {
            'balance_general' => $service->balanceGeneral(),
            'estado_resultados' => $service->estadoResultados(),
            'balance_comprobacion' => $service->balanceComprobacion(),
            'flujo_efectivo' => $service->flujoEfectivo(),
            'cambios_patrimonio' => $service->cambiosPatrimonio(),
            'estado_resultados_integral' => $service->estadoResultadosIntegral(),
        };

        $report = FinancialReport::create([
            'customer_id' => $cliente->id,
            'report_type' => $data['report_type'],
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
            'tasa_impuestos' => (float) ($data['tasa_impuestos'] ?? 0),
            'payload' => $payload,
            'generated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'report_id' => $report->id,
            'payload' => $payload,
        ]);
    }
}
