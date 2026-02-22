<?php

namespace App\Exports;

use App\Services\PdfFallbackService;

class RatiosFinancierosPDF
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function generate()
    {
        return app(PdfFallbackService::class)->stream(
            view: 'exports.ratios-financieros-pdf',
            data: ['data' => $this->data],
            baseFileName: 'Ratios_Financieros_' . now()->format('Y-m-d'),
        );
    }

    public function download()
    {
        return app(PdfFallbackService::class)->download(
            view: 'exports.ratios-financieros-pdf',
            data: ['data' => $this->data],
            baseFileName: 'Ratios_Financieros_' . now()->format('Y-m-d'),
        );
    }
}
