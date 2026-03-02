<?php

namespace App\Http\Controllers;

use App\Exports\FinancialStatementsExport;
use App\Exports\BalanceComprobacionExport;
use App\Exports\EstadoResultadosExport;
use App\Exports\BalanceGeneralExport;
use App\Exports\FlujoEfectivoExport;
use App\Exports\CambiosPatrimonioExport;
use App\Exports\EstadoResultadosIntegralExport;
use App\Models\FinancialReport;
use Maatwebsite\Excel\Facades\Excel;

class FinancialReportExportController extends Controller
{
    public function excel(int $reportId)
{
    $report = FinancialReport::with('customer')->findOrFail($reportId);
    $payload = $report->payload;

    $fileName = 'Reporte_' . $report->report_type . '_' . now()->format('Y-m-d') . '.xlsx';

    $export = match ($report->report_type) {
        'balance_general' => new BalanceGeneralExport($report->customer, $payload, $report->fecha_fin),
        'estado_resultados' => new EstadoResultadosExport($report->customer, $payload, $report->fecha_inicio, $report->fecha_fin),
        'flujo_efectivo' => new FlujoEfectivoExport($report->customer, $payload, $report->fecha_inicio, $report->fecha_fin),
        'cambios_patrimonio' => new CambiosPatrimonioExport($report->customer, $payload, $report->fecha_inicio, $report->fecha_fin),
        'financial_statements' => new FinancialStatementsExport($report->customer, $payload['data'] ?? [], $payload['balance'] ?? [], $payload['estado_resultados'] ?? []),
        'balance_comprobacion' => new BalanceComprobacionExport($report->customer, $payload, $report->fecha_fin),
        'estado_resultados_integral' => new EstadoResultadosIntegralExport($report->customer, $payload, $report->fecha_inicio, $report->fecha_fin),
        default => abort(422, 'Tipo de reporte no soportado para export Excel.'),
    };

    return Excel::download($export, $fileName);
}
}