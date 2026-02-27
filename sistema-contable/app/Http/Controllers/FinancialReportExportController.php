<?php

namespace App\Http\Controllers;

use App\Exports\FinancialStatementsExport;
use App\Models\FinancialReport;
use Maatwebsite\Excel\Facades\Excel;

class FinancialReportExportController extends Controller
{
    public function excel(int $reportId)
    {
        $report = FinancialReport::with('customer')->findOrFail($reportId);

        $payload = $report->payload;

        $balance = $payload['balance_general'] ?? ($report->report_type === 'balance_general' ? $payload : []);
        $estadoResultados = $payload['estado_resultados'] ?? ($report->report_type === 'estado_resultados' ? $payload : []);

        $data = [
            'report_type' => $report->report_type,
            'fecha_inicio' => optional($report->fecha_inicio)->format('Y-m-d'),
            'fecha_fin' => optional($report->fecha_fin)->format('Y-m-d'),
            'generated_at' => optional($report->generated_at)->format('Y-m-d H:i:s'),
        ];

        $fileName = 'Reporte_' . $report->report_type . '_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new FinancialStatementsExport($report->customer, $data, $balance, $estadoResultados),
            $fileName
        );
    }
}