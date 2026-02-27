<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\EstadoFinancieroService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancialStatementsExport;

class FinancialExportController extends Controller
{
    /**
     * Exportar PDF
     */
    public function pdf(Request $request)
    {
        $data = $request->validate([
            'customer_id'    => ['required', 'integer'],
            'fecha_inicio'   => ['required', 'date'],
            'fecha_fin'      => ['required', 'date'],
            'tasa_impuestos' => ['nullable', 'numeric'],
        ]);

        if ($data['fecha_inicio'] > $data['fecha_fin']) {
            abort(422, 'La fecha inicio no puede ser mayor que la fecha fin.');
        }

        $service = app(EstadoFinancieroService::class)
            ->setCliente((int) $data['customer_id'])
            ->setTasaImpuestos((float) ($data['tasa_impuestos'] ?? 0))
            ->setFechas($data['fecha_inicio'], $data['fecha_fin']);

        $balance = $service->balanceGeneral();
        $estadoResultados = $service->estadoResultados();
        $customer = Customer::find($data['customer_id']);

        $filename = sprintf(
            'estados_financieros_%s_%s_a_%s.pdf',
            str($customer?->name ?? 'cliente')->slug('_'),
            $data['fecha_inicio'],
            $data['fecha_fin'],
        );

        $pdf = Pdf::loadView('reports.financial-statements-pdf', [
            'customer'          => $customer,
            'data'              => $data,
            'balance'           => $balance,
            'estadoResultados'  => $estadoResultados,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename
        );
    }

    /**
     * Exportar Excel
     */
    public function excel(Request $request)
    {
        $data = $request->validate([
            'customer_id'    => ['required', 'integer'],
            'fecha_inicio'   => ['required', 'date'],
            'fecha_fin'      => ['required', 'date'],
            'tasa_impuestos' => ['nullable', 'numeric'],
        ]);

        if ($data['fecha_inicio'] > $data['fecha_fin']) {
            abort(422, 'La fecha inicio no puede ser mayor que la fecha fin.');
        }

        $service = app(EstadoFinancieroService::class)
            ->setCliente((int) $data['customer_id'])
            ->setTasaImpuestos((float) ($data['tasa_impuestos'] ?? 0))
            ->setFechas($data['fecha_inicio'], $data['fecha_fin']);

        $balance = $service->balanceGeneral();
        $estadoResultados = $service->estadoResultados();
        $customer = Customer::find($data['customer_id']);

        $filename = sprintf(
            'estados_financieros_%s_%s_a_%s.xlsx',
            str($customer?->name ?? 'cliente')->slug('_'),
            $data['fecha_inicio'],
            $data['fecha_fin'],
        );

        return Excel::download(
            new FinancialStatementsExport(
                $customer,
                $data,
                $balance,
                $estadoResultados
            ),
            $filename
        );
    }
}