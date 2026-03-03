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

class FlujoEfectivoExport implements FromArray, WithEvents, WithColumnFormatting, ShouldAutoSize
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
        $rows[] = ['Reporte:', 'Estado de Flujos de Efectivo'];
        $rows[] = ['Período:', "Del {$inicio} al {$fin}"];
        $rows[] = ['Generado:', $generatedAt];
        $rows[] = [];

        // Tabla
        $rows[] = ['Concepto', 'Monto'];

        // Operación
        $rows[] = ['ACTIVIDADES DE OPERACIÓN', ''];
        $rows[] = ['Utilidad neta', (float)($this->payload['utilidad_neta'] ?? 0)];
        $rows[] = ['Variación capital de trabajo', (float)($this->payload['variacion_capital_trabajo'] ?? 0)];
        $rows[] = ['Flujo neto de actividades de operación', (float)($this->payload['flujo_operativo'] ?? 0)];

        $rows[] = ['', ''];

        // Inversión
        $rows[] = ['ACTIVIDADES DE INVERSIÓN', ''];
        $rows[] = ['Flujo de inversión', (float)($this->payload['flujo_inversion'] ?? 0)];

        $rows[] = ['', ''];

        // Financiamiento
        $rows[] = ['ACTIVIDADES DE FINANCIAMIENTO', ''];
        $rows[] = ['Flujo de financiamiento', (float)($this->payload['flujo_financiamiento'] ?? 0)];

        $rows[] = ['', ''];

        // Totales
        $rows[] = ['FLUJO NETO DEL PERÍODO', (float)($this->payload['flujo_neto'] ?? 0)];
        $rows[] = ['EFECTIVO FINAL', (float)($this->payload['efectivo_final'] ?? 0)];

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

                // Negrita para “secciones” y “totales”
                $boldWords = [
                    'ACTIVIDADES DE OPERACIÓN',
                    'ACTIVIDADES DE INVERSIÓN',
                    'ACTIVIDADES DE FINANCIAMIENTO',
                    'FLUJO NETO DEL PERÍODO',
                    'EFECTIVO FINAL',
                    'Flujo neto de actividades de operación',
                ];

                for ($r = $headerRow + 1; $r <= $lastRow; $r++) {
                    $a = trim((string)$sheet->getCell("A{$r}")->getValue());
                    if (in_array($a, $boldWords, true)) {
                        $sheet->getStyle("A{$r}:B{$r}")->getFont()->setBold(true);
                    }
                }
            },
        ];
    }
}