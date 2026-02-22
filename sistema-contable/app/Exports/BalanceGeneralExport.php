<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BalanceGeneralExport implements FromArray, WithStyles, WithEvents
{
    protected $data;
    protected $fecha;

    public function __construct($data)
    {
        $this->data = $data;
        $this->fecha = $data['fecha'] ?? now()->format('d-m-Y');
    }

    public function array(): array
    {
        $rows = [];

        // ===== ENCABEZADO EMPRESARIAL =====
        $rows[] = ['TRANSPORTES Y SERVICIOS PEREZ Y ORTIZ DEL ATLANTICO S.A.'];
        $rows[] = ['Cédula Jurídica: 3-101-752653'];
        $rows[] = ['ESTADO DE SITUACIÓN FINANCIERA'];
        $rows[] = ["Al {$this->fecha}"];
        $rows[] = [''];
        $rows[] = [''];

        // ===== ACTIVOS =====
        $rows[] = ['ACTIVOS'];
        $rows[] = [''];

        // Activos Corrientes
        $rows[] = ['ACTIVOS CORRIENTES'];
        foreach ($this->data['detalles'] as $detalle) {
            if ($detalle['tipo'] === 'Activo') {
                $codigo = (int)$detalle['codigo'];
                if ($codigo >= 1100 && $codigo < 1200) {
                    $rows[] = [
                        $detalle['codigo'],
                        $detalle['nombre'],
                        $detalle['saldo']
                    ];
                }
            }
        }

        $rows[] = ['', 'TOTAL ACTIVOS CORRIENTES',
            $this->data['activos']['activos_circulantes'] ?? 0
        ];
        $rows[] = [''];

        // Activos No Corrientes
        $rows[] = ['ACTIVOS NO CORRIENTES'];
        foreach ($this->data['detalles'] as $detalle) {
            if ($detalle['tipo'] === 'Activo') {
                $codigo = (int)$detalle['codigo'];
                if ($codigo >= 1200) {
                    $rows[] = [
                        $detalle['codigo'],
                        $detalle['nombre'],
                        $detalle['saldo']
                    ];
                }
            }
        }

        $rows[] = ['', 'TOTAL ACTIVOS NO CORRIENTES',
            $this->data['activos']['activos_no_circulantes'] ?? 0
        ];

        $rows[] = ['', 'TOTAL ACTIVOS',
            $this->data['total_activos']
        ];

        $rows[] = [''];
        $rows[] = [''];

        // ===== PASIVOS =====
        $rows[] = ['PASIVOS'];
        $rows[] = ['PASIVOS CORRIENTES'];

        foreach ($this->data['detalles'] as $detalle) {
            if ($detalle['tipo'] === 'Pasivo') {
                $codigo = (int)$detalle['codigo'];
                if ($codigo >= 2100 && $codigo < 2200) {
                    $rows[] = [
                        $detalle['codigo'],
                        $detalle['nombre'],
                        $detalle['saldo']
                    ];
                }
            }
        }

        $rows[] = ['', 'TOTAL PASIVOS CORRIENTES',
            $this->data['pasivos']['pasivos_circulantes'] ?? 0
        ];

        $rows[] = ['PASIVOS NO CORRIENTES'];

        foreach ($this->data['detalles'] as $detalle) {
            if ($detalle['tipo'] === 'Pasivo') {
                $codigo = (int)$detalle['codigo'];
                if ($codigo >= 2200) {
                    $rows[] = [
                        $detalle['codigo'],
                        $detalle['nombre'],
                        $detalle['saldo']
                    ];
                }
            }
        }

        $rows[] = ['', 'TOTAL PASIVOS NO CORRIENTES',
            $this->data['pasivos']['pasivos_no_circulantes'] ?? 0
        ];

        $rows[] = ['', 'TOTAL PASIVOS',
            $this->data['pasivos']['total']
        ];

        $rows[] = [''];
        $rows[] = [''];

        // ===== PATRIMONIO =====
        $rows[] = ['PATRIMONIO'];

        foreach ($this->data['detalles'] as $detalle) {
            if ($detalle['tipo'] === 'Patrimonio') {
                $rows[] = [
                    $detalle['codigo'],
                    $detalle['nombre'],
                    $detalle['saldo']
                ];
            }
        }

        $rows[] = ['', 'TOTAL PATRIMONIO',
            $this->data['patrimonio']['total']
        ];

        $rows[] = ['', 'TOTAL PASIVO MÁS PATRIMONIO',
            $this->data['total_pasivos_patrimonio']
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            3 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet;

                // Centrar encabezado
                $sheet->mergeCells('A1:C1');
                $sheet->mergeCells('A2:C2');
                $sheet->mergeCells('A3:C3');
                $sheet->mergeCells('A4:C4');

                $sheet->getStyle('A1:A4')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Formato moneda
                $sheet->getStyle('C:C')->getNumberFormat()
                    ->setFormatCode('"₡"#,##0.00');

                // Ajustar ancho columnas
                $sheet->getColumnDimension('A')->setWidth(15);
                $sheet->getColumnDimension('B')->setWidth(40);
                $sheet->getColumnDimension('C')->setWidth(20);

                // Negritas en totales
                foreach (range(1, $sheet->getHighestRow()) as $row) {
                    $cell = $sheet->getCell("B{$row}")->getValue();
                    if (str_contains($cell, 'TOTAL')) {
                        $sheet->getStyle("A{$row}:C{$row}")
                            ->getFont()->setBold(true);
                    }
                }
            },
        ];
    }
}