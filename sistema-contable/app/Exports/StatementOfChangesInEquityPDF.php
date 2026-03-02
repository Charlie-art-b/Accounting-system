<?php

namespace App\Exports;

use App\Services\PdfFallbackService;

class StatementOfChangesInEquityPDF
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
            'data' => $this->data,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'cliente' => $this->cliente,
        ];
    }

    public function stream()
    {
        return app(PdfFallbackService::class)->stream(
            view: 'exports.statement-of-changes-in-equity-pdf',
            data: $this->viewData(),
            baseFileName: pathinfo($this->fileName(), PATHINFO_FILENAME),
            paper: 'a4',
            orientation: 'portrait',
        );
    }

    public function download(?string $fileName = null)
    {
        $base = $fileName
            ? pathinfo($fileName, PATHINFO_FILENAME)
            : pathinfo($this->fileName(), PATHINFO_FILENAME);

        return app(PdfFallbackService::class)->download(
            view: 'exports.statement-of-changes-in-equity-pdf',
            data: $this->viewData(),
            baseFileName: $base,
            paper: 'a4',
            orientation: 'portrait',
        );
    }

    protected function fileName(): string
    {
        return 'Statement_Of_Changes_In_Equity_' . now()->format('Y-m-d_H-i-s');
    }
}