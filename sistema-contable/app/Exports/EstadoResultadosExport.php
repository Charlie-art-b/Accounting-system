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

class EstadoResultadosExport implements FromArray, WithEvents, WithColumnFormatting, ShouldAutoSize
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
        // Prioriza fechas del payload si vienen ahí
        $inicioRaw = $this->payload['fecha_inicio'] ?? $this->fechaInicio ?? now();
        $finRaw    = $this->payload['fecha_fin'] ?? $this->fechaFin ?? now();

        $inicio = Carbon::parse($inicioRaw)->locale('es')->translatedFormat('d \d\e F Y');
        $fin    = Carbon::parse($finRaw)->locale('es')->translatedFormat('d \d\e F Y');

        $generatedAt = Carbon::now()->format('d/m/Y H:i');

        $rows = [];

        // Header
        $rows[] = ['CAHEN'];
        $rows[] = ['Servicios Contables'];
        $rows[] = ['Cliente:', $this->cliente->nombre ?? $this->cliente->name ?? '—'];
        $rows[] = ['Cédula:', $this->cliente->identification ?? '—'];
        $rows[] = ['Reporte:', 'Estado de Resultados'];
        $rows[] = ['Período:', "Del {$inicio} al {$fin}"];
        $rows[] = ['Generado:', $generatedAt];
        $rows[] = [];

        // Tabla
        $rows[] = ['Concepto', 'Monto'];

        $ingresosDetalles = $this->payload['ingresos']['detalles'] ?? [];
        $ingresosTotal    = (float)($this->payload['ingresos']['total'] ?? 0);

        $gastosDetalles = $this->payload['gastos']['detalles'] ?? [];
        $gastosTotal    = (float)($this->payload['gastos']['total'] ?? 0);

        $utilidadBruta = (float)($this->payload['utilidad_bruta'] ?? 0);
        $impuestos     = (float)($this->payload['impuestos'] ?? 0);
        $utilidadNeta  = (float)($this->payload['utilidad_neta'] ?? 0);
        $margenNeto    = (float)($this->payload['margen_neto'] ?? 0);

        // INGRESOS
        $rows[] = ['INGRESOS', ''];

        foreach ($ingresosDetalles as $r) {
            $rows[] = [
                $r['nombre'] ?? '',
                (float)($r['monto'] ?? 0),
            ];
        }

        $rows[] = ['TOTAL INGRESOS', $ingresosTotal];
        $rows[] = ['', ''];

        // GASTOS
        $rows[] = ['GASTOS', ''];

        foreach ($gastosDetalles as $g) {
            $rows[] = [
                $g['nombre'] ?? '',
                (float)($g['monto'] ?? 0),
            ];
        }

        $rows[] = ['TOTAL GASTOS', $gastosTotal];
        $rows[] = ['', ''];

        // RESULTADOS
        $rows[] = ['UTILIDAD BRUTA', $utilidadBruta];
        $rows[] = ['IMPUESTOS', $impuestos];
        $rows[] = ['UTILIDAD NETA', $utilidadNeta];
        $rows[] = ['MARGEN NETO (%)', $margenNeto]; // porcentaje

        return $rows;
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER_00, // montos
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
                    'INGRESOS',
                    'GASTOS',
                    'TOTAL INGRESOS',
                    'TOTAL GASTOS',
                    'UTILIDAD BRUTA',
                    'UTILIDAD NETA',
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