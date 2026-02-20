<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EstadoResultadosExport implements FromArray, WithHeadings, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['INGRESOS'];
        foreach ($this->data['ingresos']['detalles'] as $ingreso) {
            $rows[] = [$ingreso['codigo'], $ingreso['nombre'], number_format($ingreso['monto'], 2, '.', ',')];
        }
        $rows[] = ['', 'TOTAL INGRESOS', number_format($this->data['ingresos']['total'], 2, '.', ',')];

        $rows[] = [''];
        $rows[] = ['GASTOS'];
        foreach ($this->data['gastos']['detalles'] as $gasto) {
            $rows[] = [$gasto['codigo'], $gasto['nombre'], number_format($gasto['monto'], 2, '.', ',')];
        }
        $rows[] = ['', 'TOTAL GASTOS', number_format($this->data['gastos']['total'], 2, '.', ',')];

        $rows[] = [''];
        $rows[] = ['', 'UTILIDAD BRUTA', number_format($this->data['utilidad_bruta'], 2, '.', ',')];
        $rows[] = ['', 'IMPUESTOS', number_format($this->data['impuestos'], 2, '.', ',')];
        $rows[] = ['', 'UTILIDAD NETA', number_format($this->data['utilidad_neta'], 2, '.', ',')];
        $rows[] = ['', 'MARGEN NETO (%)', number_format($this->data['margen_neto'], 2, '.', ',')];

        return $rows;
    }

    public function headings(): array
    {
        return ['CÓDIGO', 'CONCEPTO', 'MONTO (₡)'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
        ];
    }
}