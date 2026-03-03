<?php

namespace App\Exports;

use App\Services\PdfFallbackService;

class EstadoResultadosPDF
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

    protected function viewData(): array
    {
        return [
            'estadoResultados' => $this->data,

            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            
            'cliente' => $this->cliente,
        ];
    }

    public function stream()
    {
        return app(PdfFallbackService::class)->stream(
            view: 'exports.estado-resultados-pdf',
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

    public function download()
    {
        return app(PdfFallbackService::class)->download(
            view: 'exports.estado-resultados-pdf',
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

    protected function fileName(): string
    {
        return 'Estado_Resultados_' . now()->format('Y-m-d_H-i-s');
    }
}