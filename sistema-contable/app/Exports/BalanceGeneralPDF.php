<?php

namespace App\Exports;

use App\Services\PdfFallbackService;

class BalanceGeneralPDF
{
    protected $data;
    protected $fechaInicio;
    protected $fechaFin;
    protected $cliente;
    public function __construct($data, $fechaInicio, $fechaFin, $cliente)
    {
        $this->data = $data;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->cliente = $cliente;
    }

    /**
     * Construye el PDF con configuración personalizada
     */
    protected function viewData(): array
    {
        return Pdf::loadView('exports.balance-general-pdf', [
                'data' => $this->data,
                'fechaInicio' => $this->fechaInicio,
                'fechaFin' => $this->fechaFin,
                'cliente' => $this->cliente,
            ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'dpi' => 110,
            ]);
    }

    /**
     * Mostrar en navegador
     */
    public function stream()
    {
        return app(PdfFallbackService::class)->stream(
            view: 'exports.balance-general-pdf',
            data: $this->viewData(),
            baseFileName: pathinfo($this->fileName(), PATHINFO_FILENAME),
            paper: 'a4',
            orientation: 'portrait',
            options: [
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'dpi' => 110,
            ],
        );
    }

    /**
     * Descargar directamente
     */
    public function download()
    {
        return app(PdfFallbackService::class)->download(
            view: 'exports.balance-general-pdf',
            data: $this->viewData(),
            baseFileName: pathinfo($this->fileName(), PATHINFO_FILENAME),
            paper: 'a4',
            orientation: 'portrait',
            options: [
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'dpi' => 110,
            ],
        );
    }

    /**
     * Nombre dinámico del archivo
     */
    protected function fileName(): string
    {
        return 'Balance_General_' . now()->format('Y-m-d_H-i-s') . '.pdf';
    }
}
