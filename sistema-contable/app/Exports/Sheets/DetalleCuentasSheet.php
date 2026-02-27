<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class DetalleCuentasSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(public array $balance) {}

    public function title(): string
    {
        return 'Detalle Cuentas';
    }

    public function headings(): array
    {
        return ['Código', 'Cuenta', 'Tipo', 'Clasificación', 'Saldo'];
    }

    public function array(): array
    {
        $rows = [];

        foreach (($this->balance['detalles'] ?? []) as $d) {
            $rows[] = [
                $d['codigo'] ?? '',
                $d['nombre'] ?? '',
                $d['tipo'] ?? '',
                str_replace('_', ' ', $d['clasificacion'] ?? ''),
                (float) ($d['saldo'] ?? 0),
            ];
        }

        return $rows;
    }
}