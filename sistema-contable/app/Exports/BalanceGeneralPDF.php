<?php

namespace App\Exports;

use Barryvdh\DomPDF\Facade\Pdf;

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
    protected function buildPdf()
    {
        return Pdf::loadView('exports.balance-general-pdf', [
                'data' => $this->data,
                'fecha' => $this->fecha,
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
        return $this->buildPdf()
            ->stream($this->fileName());
    }

    /**
     * Descargar directamente
     */
    public function download()
    {
        return $this->buildPdf()
            ->download($this->fileName());
    }

    /**
     * Nombre dinámico del archivo
     */
    protected function fileName(): string
    {
        return 'Balance_General_' . now()->format('Y-m-d_H-i-s') . '.pdf';
    }
}