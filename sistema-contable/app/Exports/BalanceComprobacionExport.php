<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BalanceComprobacionExport implements FromArray, WithHeadings, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->data['cuentas'] as $cuenta) {
            $rows[] = [
                $cuenta['codigo'],
                $cuenta['nombre'],
                $cuenta['debe'],
                $cuenta['haber'],
                $cuenta['saldo']
            ];
        }

        $rows[] = ['', '', $this->data['total_debe'], $this->data['total_haber']];

        return $rows;
    }

    public function headings(): array
    {
        return ['CÓDIGO', 'CUENTA', 'DÉBITO (₡)', 'CRÉDITO (₡)', 'SALDO (₡)'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}