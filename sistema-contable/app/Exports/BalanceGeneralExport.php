<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BalanceGeneralExport implements FromArray, WithHeadings, WithStyles
{
    protected $data;
    protected $fecha;

    public function __construct($data)
    {
        $this->data = $data;
        $this->fecha = $data['fecha'] ?? now()->format('Y-m-d');
    }

    public function array(): array
    {
        $rows = [];

        // Activos Circulantes
        $rows[] = ['ACTIVOS CIRCULANTES'];
        foreach ($this->data['detalles'] as $detalle) {
            if ($detalle['tipo'] === 'Activo') {
                // Classifica por código (1100-1199 = circulantes)
                $codigo = (int)$detalle['codigo'];
                if ($codigo >= 1100 && $codigo < 1200) {
                    $rows[] = [
                        $detalle['codigo'],
                        $detalle['nombre'],
                        number_format($detalle['saldo'], 2, '.', ',')
                    ];
                }
            }
        }
        $rows[] = ['', 'Subtotal Activos Circulantes', number_format($this->data['activos']['activos_circulantes'] ?? 0, 2, '.', ',')];

        // Activos No Circulantes
        $rows[] = ['ACTIVOS NO CIRCULANTES'];
        foreach ($this->data['detalles'] as $detalle) {
            if ($detalle['tipo'] === 'Activo') {
                $codigo = (int)$detalle['codigo'];
                if ($codigo >= 1200) {
                    $rows[] = [
                        $detalle['codigo'],
                        $detalle['nombre'],
                        number_format($detalle['saldo'], 2, '.', ',')
                    ];
                }
            }
        }
        $rows[] = ['', 'Subtotal Activos No Circulantes', number_format($this->data['activos']['activos_no_circulantes'] ?? 0, 2, '.', ',')];

        $rows[] = ['', 'TOTAL ACTIVOS', number_format($this->data['total_activos'], 2, '.', ',')];

        // Pasivos
        $rows[] = [''];
        $rows[] = ['PASIVOS CIRCULANTES'];
        foreach ($this->data['detalles'] as $detalle) {
            if ($detalle['tipo'] === 'Pasivo') {
                $codigo = (int)$detalle['codigo'];
                if ($codigo >= 2100 && $codigo < 2200) {
                    $rows[] = [
                        $detalle['codigo'],
                        $detalle['nombre'],
                        number_format($detalle['saldo'], 2, '.', ',')
                    ];
                }
            }
        }
        $rows[] = ['', 'Subtotal Pasivos Circulantes', number_format($this->data['pasivos']['pasivos_circulantes'] ?? 0, 2, '.', ',')];

        // Pasivos No Circulantes
        $rows[] = ['PASIVOS NO CIRCULANTES'];
        foreach ($this->data['detalles'] as $detalle) {
            if ($detalle['tipo'] === 'Pasivo') {
                $codigo = (int)$detalle['codigo'];
                if ($codigo >= 2200) {
                    $rows[] = [
                        $detalle['codigo'],
                        $detalle['nombre'],
                        number_format($detalle['saldo'], 2, '.', ',')
                    ];
                }
            }
        }
        $rows[] = ['', 'Subtotal Pasivos No Circulantes', number_format($this->data['pasivos']['pasivos_no_circulantes'] ?? 0, 2, '.', ',')];

        $rows[] = ['', 'TOTAL PASIVOS', number_format($this->data['pasivos']['total'], 2, '.', ',')];

        // Patrimonio
        $rows[] = [''];
        $rows[] = ['PATRIMONIO'];
        foreach ($this->data['detalles'] as $detalle) {
            if ($detalle['tipo'] === 'Patrimonio') {
                $rows[] = [
                    $detalle['codigo'],
                    $detalle['nombre'],
                    number_format($detalle['saldo'], 2, '.', ',')
                ];
            }
        }
        $rows[] = ['', 'TOTAL PATRIMONIO', number_format($this->data['patrimonio']['total'], 2, '.', ',')];

        $rows[] = ['', 'TOTAL PASIVOS + PATRIMONIO', number_format($this->data['total_pasivos_patrimonio'], 2, '.', ',')];

        return $rows;
    }

    public function headings(): array
    {
        return ['CÓDIGO', 'CUENTA', 'SALDO (₡)'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => 'center'],
            ],
            'A:C' => [
                'alignment' => ['horizontal' => 'right'],
            ],
        ];
    }
}