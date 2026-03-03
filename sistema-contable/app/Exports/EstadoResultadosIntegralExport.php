<?php

namespace App\Exports;

use App\Exports\Concerns\ExcelStandardStyles;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class EstadoResultadosIntegralExport implements FromArray, WithEvents, WithColumnFormatting, ShouldAutoSize
{
    use ExcelStandardStyles;

    public function __construct(
        private $cliente,
        private array $payload,
        private $fechaInicio,
        private $fechaFin,
    ) {}

    public function array(): array
    {
        $inicio = Carbon::parse($this->fechaInicio ?? now())
            ->locale('es')
            ->translatedFormat('d \d\e F Y');

        $fin = Carbon::parse($this->fechaFin ?? now())
            ->locale('es')
            ->translatedFormat('d \d\e F Y');

        $generatedAt = Carbon::now()->format('d/m/Y H:i');

        $rows = [];

        // Header (7 + 1 blanco) -> títulos en fila 8
        $rows[] = ['CAHEN'];
        $rows[] = ['Servicios Contables'];
        $rows[] = ['Cliente:', $this->cliente->nombre ?? $this->cliente->name ?? '—'];
        $rows[] = ['Cédula:', $this->cliente->identification ?? '—'];
        $rows[] = ['Reporte:', 'Estado de Resultados Integral'];
        $rows[] = ['Período:', "Del {$inicio} al {$fin}"];
        $rows[] = ['Generado:', $generatedAt];
        $rows[] = [];

        // Tabla
        $rows[] = ['Concepto', 'Monto'];

        $rows[] = ['TOTAL INGRESOS', (float)($this->payload['ingresos'] ?? 0)];
        $rows[] = ['GASTOS OPERATIVOS', (float)($this->payload['gastos_operativos'] ?? 0)];

        $rows[] = ['', ''];

        $rows[] = ['UTILIDAD OPERATIVA', (float)($this->payload['utilidad_antes_depreciacion'] ?? 0)];
        $rows[] = ['DEPRECIACIÓN Y AMORTIZACIÓN', (float)($this->payload['depreciacion'] ?? 0)];
        $rows[] = ['OTROS GASTOS', (float)($this->payload['otros_gastos'] ?? 0)];

        $rows[] = ['', ''];

        $rows[] = ['UTILIDAD ANTES DE IMPUESTOS', (float)($this->payload['utilidad_antes_impuestos'] ?? 0)];
        $rows[] = ['IMPUESTO SOBRE LA RENTA', (float)($this->payload['impuestos'] ?? 0)];
        $rows[] = ['UTILIDAD NETA DEL PERÍODO', (float)($this->payload['utilidad_neta'] ?? 0)];

        return $rows;
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $headerRow = 8;
                $this->applyStandardStyles($event, $headerRow, 'B');

                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                // Alinear números
                $sheet->getStyle("B" . ($headerRow + 1) . ":B{$lastRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Negrita en filas clave
                $bold = [
                    'TOTAL INGRESOS',
                    'GASTOS OPERATIVOS',
                    'UTILIDAD OPERATIVA',
                    'UTILIDAD ANTES DE IMPUESTOS',
                    'UTILIDAD NETA DEL PERÍODO',
                ];

                for ($r = $headerRow + 1; $r <= $lastRow; $r++) {
                    $a = trim((string)$sheet->getCell("A{$r}")->getValue());
                    if (in_array($a, $bold, true)) {
                        $sheet->getStyle("A{$r}:B{$r}")->getFont()->setBold(true);
                    }
                }
            },
        ];
    }
}