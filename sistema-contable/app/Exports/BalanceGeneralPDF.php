<?php

namespace App\Exports;

use App\Services\PdfFallbackService;

class BalanceGeneralPDF
{
    protected array $data;
    protected string $fecha;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->fecha = $data['fecha'] ?? now()->format('Y-m-d');
    }

    /**
     * Construye el PDF con configuración personalizada
     */
    protected function viewData(): array
    {
        return [
            'data' => $this->data,
            'fecha' => $this->fecha,
        ];
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
