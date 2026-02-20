<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RatiosFinancierosExport implements FromArray, WithHeadings, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['RATIOS DE LIQUIDEZ'];
        $rows[] = ['Razón Corriente', $this->data['liquidez']['razon_corriente'] ?? 0];

        $rows[] = [''];
        $rows[] = ['RATIOS DE SOLVENCIA'];
        $rows[] = ['Razón de Deuda', $this->data['solvencia']['razon_deuda'] ?? 0];

        $rows[] = [''];
        $rows[] = ['RATIOS DE RENTABILIDAD'];
        $rows[] = ['Margen Neto (%)', $this->data['rentabilidad']['margen_neto'] ?? 0];
        $rows[] = ['ROA (%)', $this->data['rentabilidad']['roa'] ?? 0];
        $rows[] = ['ROE (%)', $this->data['rentabilidad']['roe'] ?? 0];

        $rows[] = [''];
        $rows[] = ['RATIOS DE EFICIENCIA'];
        $rows[] = ['Rotación de Activos', $this->data['eficiencia']['rotacion_activo'] ?? 0];

        return $rows;
    }

    public function headings(): array
    {
        return ['RATIO', 'VALOR'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}