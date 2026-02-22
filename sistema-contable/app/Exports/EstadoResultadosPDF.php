<?php

namespace App\Exports;

use App\Services\PdfFallbackService;

class EstadoResultadosPDF
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function generate()
    {
        return app(PdfFallbackService::class)->stream(
            view: 'exports.estado-resultados-pdf',
            data: ['data' => $this->data],
            baseFileName: 'Estado_Resultados_' . now()->format('Y-m-d'),
        );
    }

    public function download()
    {
        return app(PdfFallbackService::class)->download(
            view: 'exports.estado-resultados-pdf',
            data: ['data' => $this->data],
            baseFileName: 'Estado_Resultados_' . now()->format('Y-m-d'),
        );
    }
}
