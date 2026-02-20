<?php

namespace App\Exports;

use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;

class BalanceGeneralPDF
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function generate()
    {
        $pdf = PDF::loadView('exports.balance-general-pdf', [
            'data' => $this->data,
            'fecha' => $this->data['fecha'] ?? now()->format('Y-m-d'),
        ]);

        return $pdf->stream('Balance_General_' . now()->format('Y-m-d') . '.pdf');
    }

    public function download()
    {
        $pdf = PDF::loadView('exports.balance-general-pdf', [
            'data' => $this->data,
            'fecha' => $this->data['fecha'] ?? now()->format('Y-m-d'),
        ]);

        return $pdf->download('Balance_General_' . now()->format('Y-m-d') . '.pdf');
    }
}