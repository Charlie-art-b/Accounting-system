<?php


namespace App\Http\Controllers;

use App\Exports\BalanceGeneralPDF;
use Illuminate\Http\Request;

class PDFTestController extends Controller
{
    /**
     * Genera un PDF de prueba con datos ficticios
     */
// ...existing code...
 
 public function balanceGeneralTest()
{
    $data = [
        'fecha' => now()->format('Y-m-d'),

        'detalles' => [

            // ================= ACTIVO CORRIENTE =================
            [
                'codigo' => '1100',
                'nombre' => 'Caja',
                'tipo' => 'Activo',
                'clasificacion' => 'activo_corriente',
                'saldo' => 5000000,
                'saldo_anterior' => 3000000,
                'nota' => '1',
            ],
            [
                'codigo' => '1200',
                'nombre' => 'Cuentas por Cobrar',
                'tipo' => 'Activo',
                'clasificacion' => 'activo_corriente',
                'saldo' => 8000000,
                'saldo_anterior' => 6000000,
                'nota' => '2',
            ],

            // ================= ACTIVO NO CORRIENTE =================
            [
                'codigo' => '1500',
                'nombre' => 'Propiedad, Planta y Equipo',
                'tipo' => 'Activo',
                'clasificacion' => 'activo_no_corriente',
                'saldo' => 40000000,
                'saldo_anterior' => 38000000,
                'nota' => '3',
            ],

            // ================= PASIVO CORRIENTE =================
            [
                'codigo' => '2100',
                'nombre' => 'Proveedores',
                'tipo' => 'Pasivo',
                'clasificacion' => 'pasivo_corriente',
                'saldo' => 7000000,
                'saldo_anterior' => 6000000,
                'nota' => '4',
            ],

            // ================= PASIVO NO CORRIENTE =================
            [
                'codigo' => '2500',
                'nombre' => 'Préstamo Bancario Largo Plazo',
                'tipo' => 'Pasivo',
                'clasificacion' => 'pasivo_no_corriente',
                'saldo' => 20000000,
                'saldo_anterior' => 25000000,
                'nota' => '5',
            ],

            // ================= PATRIMONIO =================
            [
                'codigo' => '3100',
                'nombre' => 'Capital Social',
                'tipo' => 'Patrimonio',
                'clasificacion' => 'patrimonio',
                'saldo' => 10000000,
                'saldo_anterior' => 10000000,
                'nota' => '6',
            ],
            [
                'codigo' => '3200',
                'nombre' => 'Utilidad del Periodo',
                'tipo' => 'Patrimonio',
                'clasificacion' => 'patrimonio',
                'saldo' => 6000000,
                'saldo_anterior' => 6000000,
                'nota' => '7',
            ],
        ],

        // ===== TOTALES CORRECTOS =====
        'activos' => [
            'activos_circulantes' => 13000000,
            'activos_no_circulantes' => 40000000,
            'total' => 53000000,
        ],

        'pasivos' => [
            'pasivos_circulantes' => 7000000,
            'pasivos_no_circulantes' => 20000000,
            'total' => 27000000,
        ],

        'patrimonio' => [
            'total' => 26000000,
        ],

        'total_activos' => 53000000,
        'total_activos_anterior' => 47000000,
        'total_pasivos_patrimonio' => 53000000,
        'ecuacion_balanceada' => true,
        'diferencia' => 0,
    ];

    return (new BalanceGeneralPDF($data))->stream();
}
}