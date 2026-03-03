<?php

namespace App\Exports\Concerns;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

trait ExcelStandardStyles
{
    /**
     * Aplica estilo estándar de reporte.
     *
     * @param AfterSheet $event
     * @param int $headerRow Fila donde está el encabezado de la tabla (por ejemplo 9)
     * @param string $lastColumn Última columna usada (por ejemplo 'E' o 'B')
     */
    protected function applyStandardStyles(AfterSheet $event, int $headerRow, string $lastColumn): void
    {
        $sheet = $event->sheet->getDelegate();
        $lastRow = $sheet->getHighestRow();

        // Freeze: deja fijo todo lo de arriba del header
        $sheet->freezePane('A' . ($headerRow + 1));

        // Título (A1..lastColumn1 y A2..lastColumn2)
        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->mergeCells("A2:{$lastColumn}2");
        $sheet->getStyle("A1")->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle("A2")->getFont()->setSize(11);

        // Labels del header (A3:A7)
        $sheet->getStyle("A3:A7")->getFont()->setBold(true);

        // Cabecera de tabla
        $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F2F3F5'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D0D0D0'],
                ],
            ],
        ]);

        // Bordes para toda la tabla
        $sheet->getStyle("A{$headerRow}:{$lastColumn}{$lastRow}")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Totales: última fila (asumimos que la última fila de la tabla es el total)
        $sheet->getStyle("A{$lastRow}:{$lastColumn}{$lastRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F2F3F8'],
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '111111'],
                ],
            ],
        ]);
    }
}