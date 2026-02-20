<?php

namespace App\Exports;

use Barryvdh\DomPDF\Facade\Pdf;

class RatiosFinancierosPDF
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function generate()
    {
        $pdf = PDF::loadView('exports.ratios-financieros-pdf', [
            'data' => $this->data,
        ]);

        return $pdf->stream('Ratios_Financieros_' . now()->format('Y-m-d') . '.pdf');
    }

    public function download()
    {
        $pdf = PDF::loadView('exports.ratios-financieros-pdf', [
            'data' => $this->data,
        ]);

        return $pdf->download('Ratios_Financieros_' . now()->format('Y-m-d') . '.pdf');
    }
}