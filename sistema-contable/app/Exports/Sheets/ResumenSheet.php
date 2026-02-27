<?php

namespace App\Exports\Sheets;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class ResumenSheet implements FromArray, WithTitle
{
    public function __construct(
        public ?Customer $customer,
        public array $data,
        public array $balance,
        public array $estadoResultados,
    ) {}

    public function title(): string
    {
        return 'Resumen';
    }

    public function array(): array
    {
        return [
            ['Estados Financieros'],
            ['Cliente', $this->customer?->name ?? 'N/D'],
            ['Fecha inicio', $this->data['fecha_inicio'] ?? ''],
            ['Fecha fin', $this->data['fecha_fin'] ?? ''],
            ['Tasa impuestos (%)', $this->data['tasa_impuestos'] ?? 0],
            [],
            ['Balance General'],
            ['Total Activos', $this->balance['total_activos'] ?? 0],
            ['Total Pasivos', $this->balance['pasivos']['total'] ?? 0],
            ['Total Patrimonio', $this->balance['patrimonio']['total'] ?? 0],
            ['Balanceado', ($this->balance['ecuacion_balanceada'] ?? false) ? 'Sí' : 'No'],
            ['Diferencia', $this->balance['diferencia'] ?? 0],
            [],
            ['Estado de Resultados'],
            ['Total Ingresos', $this->estadoResultados['ingresos']['total'] ?? 0],
            ['Total Gastos', $this->estadoResultados['gastos']['total'] ?? 0],
            ['Impuestos', $this->estadoResultados['impuestos'] ?? 0],
            ['Utilidad Neta', $this->estadoResultados['utilidad_neta'] ?? 0],
            ['Margen Neto (%)', $this->estadoResultados['margen_neto'] ?? 0],
        ];
    }
}