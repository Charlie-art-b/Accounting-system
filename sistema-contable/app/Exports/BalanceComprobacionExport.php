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

class BalanceComprobacionExport implements FromArray, WithEvents, WithColumnFormatting, ShouldAutoSize
{
    use ExcelStandardStyles;

    public function __construct(
        private $cliente,
        private array $payload,  
        private $fechaFin,        
    ) {}

    public function array(): array
    {
        $fechaCorte = Carbon::parse($this->fechaFin ?? now())
            ->locale('es')
            ->translatedFormat('d \d\e F Y');

        $generatedAt = Carbon::now()->format('d/m/Y H:i');

        $rows = [];

        // Header (7 filas + 1 en blanco) => cabecera tabla en fila 9
        $rows[] = ['CAHEN'];
        $rows[] = ['Servicios Contables'];
        $rows[] = ['Cliente:', $this->cliente->nombre ?? $this->cliente->name ?? '—'];
        $rows[] = ['Cédula:', $this->cliente->identification ?? '—'];
        $rows[] = ['Reporte:', 'Balance de Comprobación'];
        $rows[] = ['Fecha corte:', $fechaCorte];
        $rows[] = ['Generado:', $generatedAt];
        $rows[] = [];

        // Tabla
        $rows[] = ['Código', 'Cuenta', 'Clasificación', 'Débito', 'Crédito'];

        $totalDebe = 0.0;
        $totalHaber = 0.0;

        foreach (($this->payload['cuentas'] ?? []) as $c) {
            $debe = (float)($c['debe'] ?? 0);
            $haber = (float)($c['haber'] ?? 0);

            $totalDebe += $debe;
            $totalHaber += $haber;

            $rows[] = [
                $c['codigo'] ?? '',
                $c['nombre'] ?? '',
                $c['clasificacion'] ?? '',
                $debe,
                $haber,
            ];
        }

        $rows[] = ['Totales', '', '', $totalDebe, $totalHaber];

        return $rows;
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_NUMBER_00,
            'E' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                // Cabecera tabla en fila 9 (por el header que construimos en array())
                $this->applyStandardStyles($event, 8, 'E');

                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                // Alinear números
                $sheet->getStyle("D9:E{$lastRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}