<?php

namespace App\Services;

use App\Exports\BalanceGeneralExport;
use App\Exports\EstadoResultadosExport;
use App\Exports\BalanceComprobacionExport;
use App\Exports\RatiosFinancierosExport;
use App\Exports\BalanceGeneralPDF;
use App\Exports\EstadoResultadosPDF;
use App\Exports\RatiosFinancierosPDF;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ExportacionesService
{
    protected EstadoFinancieroService $estadoService;

    public function __construct(EstadoFinancieroService $estadoService)
    {
        $this->estadoService = $estadoService;
    }

    /**
     * Configura el cliente y fechas
     */
    private function configurar($cliente = null, $fechaInicio = null, $fechaFin = null)
    {
        if ($cliente) {
            $this->estadoService->setCliente($cliente);
        }

        if ($fechaInicio && $fechaFin) {
            $this->estadoService->setFechas($fechaInicio, $fechaFin);
        }

        return $this->estadoService;
    }

    /**
     * Exportar Balance General a Excel
     */
    public function balanceGeneralExcel($cliente = null, $fechaInicio = null, $fechaFin = null)
    {
        $this->configurar($cliente, $fechaInicio, $fechaFin);

        $data = $this->estadoService->balanceGeneral();
        $timestamp = now()->format('Y-m-d-H-i-s');

        return Excel::download(
            new BalanceGeneralExport($data),
            "Balance_General_{$timestamp}.xlsx"
        );
    }

    /**
     * Exportar Balance General a PDF
     */
    public function balanceGeneralPDF($cliente = null, $fechaInicio = null, $fechaFin = null)
    {
        $this->configurar($cliente, $fechaInicio, $fechaFin);

        $data = $this->estadoService->balanceGeneral();
        
        return (new BalanceGeneralPDF($data))->download();
    }

    /**
     * Exportar Estado de Resultados a Excel
     */
    public function estadoResultadosExcel($cliente = null, $fechaInicio = null, $fechaFin = null)
    {
        $this->configurar($cliente, $fechaInicio, $fechaFin);

        $data = $this->estadoService->estadoResultados();
        $timestamp = now()->format('Y-m-d-H-i-s');

        return Excel::download(
            new EstadoResultadosExport($data),
            "Estado_Resultados_{$timestamp}.xlsx"
        );
    }

    /**
     * Exportar Estado de Resultados a PDF
     */
    public function estadoResultadosPDF($cliente = null, $fechaInicio = null, $fechaFin = null)
    {
        $this->configurar($cliente, $fechaInicio, $fechaFin);

        $data = $this->estadoService->estadoResultados();
        
        return (new EstadoResultadosPDF($data))->download();
    }

    /**
     * Exportar Balance de Comprobación a Excel
     */
    public function balanceComprobacionExcel($cliente = null, $fechaInicio = null, $fechaFin = null)
    {
        $this->configurar($cliente, $fechaInicio, $fechaFin);

        $data = $this->estadoService->balanceComprobacion();
        $timestamp = now()->format('Y-m-d-H-i-s');

        return Excel::download(
            new BalanceComprobacionExport($data),
            "Balance_Comprobacion_{$timestamp}.xlsx"
        );
    }

    /**
     * Exportar Ratios Financieros a Excel
     */
    public function ratiosFinancierosExcel($cliente = null, $fechaInicio = null, $fechaFin = null)
    {
        $this->configurar($cliente, $fechaInicio, $fechaFin);

        $data = $this->estadoService->ratiosFinancieros();
        $timestamp = now()->format('Y-m-d-H-i-s');

        return Excel::download(
            new RatiosFinancierosExport($data),
            "Ratios_Financieros_{$timestamp}.xlsx"
        );
    }

    /**
     * Exportar Ratios Financieros a PDF
     */
    public function ratiosFinancierosPDF($cliente = null, $fechaInicio = null, $fechaFin = null)
    {
        $this->configurar($cliente, $fechaInicio, $fechaFin);

        $data = $this->estadoService->ratiosFinancieros();
        
        return (new RatiosFinancierosPDF($data))->download();
    }
}