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

class BalanceGeneralExport implements FromArray, WithEvents, WithColumnFormatting, ShouldAutoSize
{
    use ExcelStandardStyles;

    public function __construct(
        private $cliente,
        private array $payload, // <- balance payload
        private $fechaFin,      // <- fecha corte
    ) {}

    public function array(): array
    {
        $fechaCorte = Carbon::parse($this->fechaFin ?? now())
            ->locale('es')
            ->translatedFormat('d \d\e F Y');

        $generatedAt = Carbon::now()->format('d/m/Y H:i');

        $rows = [];

        // Header (7 + 1 blanco) => títulos en fila 9
        $rows[] = ['CAHEN'];
        $rows[] = ['Servicios Contables'];
        $rows[] = ['Cliente:', $this->cliente->nombre ?? $this->cliente->name ?? '—'];
        $rows[] = ['Cédula:', $this->cliente->identification ?? '—'];
        $rows[] = ['Reporte:', 'Balance General'];
        $rows[] = ['Fecha corte:', $fechaCorte];
        $rows[] = ['Generado:', $generatedAt];
        $rows[] = [];

        // Tabla
        $rows[] = ['Concepto', 'Clasificación', 'Monto'];

        $detalles = $this->payload['detalles'] ?? [];

        // Secciones típicas
        $rows[] = ['ACTIVOS', '', ''];
        foreach ($detalles as $d) {
            if (($d['tipo'] ?? '') === 'Activo') {
                $rows[] = [
                    $d['nombre'] ?? '',
                    $d['clasificacion'] ?? '',
                    (float)($d['saldo'] ?? 0),
                ];
            }
        }
        $rows[] = ['TOTAL ACTIVOS', '', (float)($this->payload['total_activos'] ?? 0)];

        $rows[] = ['', '', ''];

        $rows[] = ['PASIVOS', '', ''];
        foreach ($detalles as $d) {
            if (($d['tipo'] ?? '') === 'Pasivo') {
                $rows[] = [
                    $d['nombre'] ?? '',
                    $d['clasificacion'] ?? '',
                    (float)($d['saldo'] ?? 0),
                ];
            }
        }
        $rows[] = ['TOTAL PASIVOS', '', (float)($this->payload['pasivos']['total'] ?? 0)];

        $rows[] = ['', '', ''];

        $rows[] = ['PATRIMONIO', '', ''];
        foreach ($detalles as $d) {
            if (($d['tipo'] ?? '') === 'Patrimonio') {
                $rows[] = [
                    $d['nombre'] ?? '',
                    $d['clasificacion'] ?? '',
                    (float)($d['saldo'] ?? 0),
                ];
            }
        }

        $rows[] = ['TOTAL PASIVO + PATRIMONIO', '', (float)($this->payload['total_pasivos_patrimonio'] ?? 0)];

        return $rows;
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $headerRow = 8;
                $this->applyStandardStyles($event, $headerRow, 'C');

                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                // Alineación numérica
                $sheet->getStyle("C" . ($headerRow + 1) . ":C{$lastRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Resaltar filas tipo "ACTIVOS / PASIVOS / PATRIMONIO"
                // (las que tienen monto vacío y texto en A)
                for ($r = $headerRow + 1; $r <= $lastRow; $r++) {
                    $a = trim((string)$sheet->getCell("A{$r}")->getValue());
                    $c = trim((string)$sheet->getCell("C{$r}")->getValue());
                    if (in_array(mb_strtoupper($a), ['ACTIVOS', 'PASIVOS', 'PATRIMONIO'], true) && $c === '') {
                        $sheet->getStyle("A{$r}:C{$r}")->getFont()->setBold(true);
                    }
                }
            },
        ];
    }
}