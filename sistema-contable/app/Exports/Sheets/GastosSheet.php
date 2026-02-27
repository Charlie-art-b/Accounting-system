<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class GastosSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(public array $estadoResultados) {}

    public function title(): string
    {
        return 'Gastos';
    }

    public function headings(): array
    {
        return ['Código', 'Cuenta', 'Monto'];
    }

    public function array(): array
    {
        $rows = [];

        foreach (($this->estadoResultados['gastos']['detalles'] ?? []) as $g) {
            $rows[] = [
                $g['codigo'] ?? '',
                $g['nombre'] ?? '',
                (float) ($g['monto'] ?? 0),
            ];
        }

        // línea final de total
        $rows[] = ['', 'TOTAL', (float) ($this->estadoResultados['gastos']['total'] ?? 0)];

        return $rows;
    }
}