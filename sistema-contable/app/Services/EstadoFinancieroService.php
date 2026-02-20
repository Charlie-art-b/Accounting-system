<?php

namespace App\Services;

use App\Models\JournalLine;
use App\Models\JournalEntry;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Servicio de Estados Financieros
 * 
 * Genera los principales estados financieros para contadores que manejan múltiples clientes:
 * - Balance General
 * - Estado de Resultados
 * - Balance de Comprobación
 * - Estado de Flujos de Efectivo
 * - Estado de Cambios en el Patrimonio
 * - Análisis de Ratios Financieros
 */
class  EstadoFinancieroService
{
    protected ?int $customerId = null;
    protected Carbon $fechaInicio;
    protected Carbon $fechaFin;
    protected ?Carbon $fechaComparativa = null;

    public function __construct()
    {
        $this->fechaInicio = Carbon::now()->startOfYear();
        $this->fechaFin = Carbon::now();
    }

    /**
     * Establece el cliente para los estados financieros
     */
    public function setCliente(int | Customer $cliente): self
    {
        $this->customerId = is_int($cliente) ? $cliente : $cliente->id;
        return $this;
    }

    
    public function setFechas(Carbon | string $inicio, Carbon | string $fin): self
    {
        $this->fechaInicio = is_string($inicio) ? Carbon::parse($inicio) : $inicio;
        $this->fechaFin = is_string($fin) ? Carbon::parse($fin) : $fin;
        return $this;
    }

    /**
     * Establece fecha comparativa (año anterior)
     */
    public function setFechaComparativa(Carbon | string $fecha): self
    {
        $this->fechaComparativa = is_string($fecha) ? Carbon::parse($fecha) : $fecha;
        return $this;
    }

    /**
     * Genera el estado financiero solicitado
     */
    public function generar(string $tipo)
    {
        return match ($tipo) {
            'balance_general' => $this->balanceGeneral(),
            'estado_resultados' => $this->estadoResultados(),
            'balance_comprobacion' => $this->balanceComprobacion(),
            'flujo_efectivo' => $this->flujoEfectivo(),
            'cambios_patrimonio' => $this->cambiosPatrimonio(),
            'ratios_financieros' => $this->ratiosFinancieros(),
            default => throw new \InvalidArgumentException("Tipo de estado financiero no válido: $tipo"),
        };
    }

    /**
     * Obtiene los saldos de las cuentas en un período específico
     */
    private function obtenerSaldoCuentas(?Carbon $fechaHasta = null): Collection
    {
        $fecha = $fechaHasta ?? $this->fechaFin;

        // Obtén todas las cuentas activas del gráfico
        $cuentas = ChartOfAccount::where('is_active', true)->get();
        
        $resultado = collect();
        
        foreach ($cuentas as $cuenta) {
            $saldos = JournalLine::selectRaw(
                'SUM(CAST(debit AS DECIMAL(15,2))) as total_debe,
                SUM(CAST(credit AS DECIMAL(15,2))) as total_haber'
            )
            ->whereHas('journalEntry', function ($q) use ($fecha) {
                $q->whereBetween('posted_at', [$this->fechaInicio, $fecha])
                  ->where(function ($query) {
                      $query->where('is_reversal', false)
                            ->orWhereNull('is_reversal');
                  });
            })
            ->where('chart_of_account_id', $cuenta->id)
            ->first();
            
            // Incluye la cuenta aunque no tenga saldos
            $cuenta->total_debe = (float) ($saldos?->total_debe ?? 0);
            $cuenta->total_haber = (float) ($saldos?->total_haber ?? 0);
            
            // Solo agrega si tiene movimientos
            if ($cuenta->total_debe > 0 || $cuenta->total_haber > 0) {
                $resultado->push($cuenta);
            }
        }
        
        return $resultado;
    }

    /**
     * BALANCE GENERAL - Muestra la posición financiera en una fecha
     */
    public function balanceGeneral(): array
{
    $saldos = $this->obtenerSaldoCuentas();
    $comparativo = null;

    if ($this->fechaComparativa) {
        $comparativo = $this->obtenerSaldoCuentas($this->fechaComparativa);
    }

    $activos = [
        'activos_circulantes' => [],
        'activos_no_circulantes' => [],
        'total' => 0,
    ];

    $pasivos = [
        'pasivos_circulantes' => [],
        'pasivos_no_circulantes' => [],
        'total' => 0,
    ];

    $patrimonio = [
        'capital_social' => [],
        'resultados' => [],
        'total' => 0,
    ];

    foreach ($saldos as $cuenta) {

        // 🔥 CORRECCIÓN IMPORTANTE: usar el tipo real de la cuenta
        $saldo = $this->calcularSaldo($cuenta, $cuenta->type);

        $cuentaComparativa = $comparativo?->firstWhere('id', $cuenta->id);

        $saldoComparativo = $cuentaComparativa
            ? $this->calcularSaldo($cuentaComparativa, $cuentaComparativa->type)
            : 0;

        $item = [
            'codigo' => $cuenta->code,
            'nombre' => $cuenta->name,
            'saldo_actual' => $saldo,
            'saldo_comparativo' => $saldoComparativo,
            'variacion' => $saldo - $saldoComparativo,
            'variacion_porcentaje' => $saldoComparativo != 0
                ? (($saldo - $saldoComparativo) / abs($saldoComparativo)) * 100
                : 0,
        ];

        switch ($cuenta->type) {

            case 'asset':
                if ($this->esCirulante($cuenta->code)) {
                    $activos['activos_circulantes'][] = $item;
                } else {
                    $activos['activos_no_circulantes'][] = $item;
                }
                $activos['total'] += $saldo;
                break;

            case 'liability':
                if ($this->esCirulante($cuenta->code)) {
                    $pasivos['pasivos_circulantes'][] = $item;
                } else {
                    $pasivos['pasivos_no_circulantes'][] = $item;
                }
                $pasivos['total'] += $saldo;
                break;

            case 'equity':
                $patrimonio['capital_social'][] = $item;
                $patrimonio['total'] += $saldo;
                break;
        }
    }

    // 🔥 Agregar utilidad del período correctamente al patrimonio
    $utilidadPeriodo = $this->obtenerUtilidadPeriodo();

    $patrimonio['resultados'][] = [
        'codigo' => 'UTIL',
        'nombre' => 'Utilidad/Pérdida del Período',
        'saldo_actual' => $utilidadPeriodo,
        'saldo_comparativo' => 0,
        'variacion' => $utilidadPeriodo,
        'variacion_porcentaje' => 0,
    ];

    $patrimonio['total'] += $utilidadPeriodo;

    $totalActivos = $activos['total'];
    $totalPasivosPatrimonio = $pasivos['total'] + $patrimonio['total'];
    $diferencia = $totalActivos - $totalPasivosPatrimonio;

    return [
        'tipo' => 'Balance General',
        'fecha' => $this->fechaFin->format('Y-m-d'),
        'fecha_inicio' => $this->fechaInicio->format('Y-m-d'),
        'activos' => $activos,
        'pasivos' => $pasivos,
        'patrimonio' => $patrimonio,
        'total_activos' => $totalActivos,
        'total_pasivos_patrimonio' => $totalPasivosPatrimonio,
        'ecuacion_balanceada' => abs($diferencia) < 0.01,
        'diferencia' => $diferencia,
    ];
}
    /**
     * ESTADO DE RESULTADOS - Muestra los ingresos, gastos y utilidad del período
     */
    public function estadoResultados(): array
    {
        $saldos = $this->obtenerSaldoCuentas();
        $comparativo = null;

        if ($this->fechaComparativa) {
            $comparativo = $this->obtenerSaldoCuentas($this->fechaComparativa);
        }

        $ingresos = [];
        $gastos = [];
        $totalIngresos = 0;
        $totalGastos = 0;

        foreach ($saldos as $cuenta) {
            if ($cuenta->type === 'revenue') {
                $saldo = $this->calcularSaldo($cuenta, 'revenue');
                $cuentaComparativa = $comparativo?->firstWhere('id', $cuenta->id);
                $saldoComparativo = $cuentaComparativa ? $this->calcularSaldo($cuentaComparativa, 'revenue') : 0;

                $ingresos[] = [
                    'codigo' => $cuenta->code,
                    'nombre' => $cuenta->name,
                    'monto_actual' => $saldo,
                    'monto_comparativo' => $saldoComparativo,
                    'variacion' => $saldo - $saldoComparativo,
                    'variacion_porcentaje' => $saldoComparativo != 0 ? (($saldo - $saldoComparativo) / $saldoComparativo) * 100 : 0,
                ];
                $totalIngresos += $saldo;
            } elseif ($cuenta->type === 'expense') {
                $saldo = $this->calcularSaldo($cuenta, 'expense');
                $cuentaComparativa = $comparativo?->firstWhere('id', $cuenta->id);
                $saldoComparativo = $cuentaComparativa ? $this->calcularSaldo($cuentaComparativa, 'expense') : 0;

                $gastos[] = [
                    'codigo' => $cuenta->code,
                    'nombre' => $cuenta->name,
                    'monto_actual' => $saldo,
                    'monto_comparativo' => $saldoComparativo,
                    'variacion' => $saldo - $saldoComparativo,
                    'variacion_porcentaje' => $saldoComparativo != 0 ? (($saldo - $saldoComparativo) / $saldoComparativo) * 100 : 0,
                ];
                $totalGastos += $saldo;
            }
        }

        $utilidadBruta = $totalIngresos - $totalGastos;
        $impuestos = $this->estimarImpuestos($utilidadBruta);
        $utilidadNeta = $utilidadBruta - $impuestos;

        return [
            'tipo' => 'Estado de Resultados',
            'fecha_inicio' => $this->fechaInicio->format('Y-m-d'),
            'fecha_fin' => $this->fechaFin->format('Y-m-d'),
            'ingresos' => [
                'detalles' => $ingresos,
                'total' => $totalIngresos,
            ],
            'gastos' => [
                'detalles' => $gastos,
                'total' => $totalGastos,
            ],
            'utilidad_bruta' => $utilidadBruta,
            'margen_bruto' => $totalIngresos != 0 ? ($utilidadBruta / $totalIngresos) * 100 : 0,
            'gastos_operacionales_total' => $totalGastos,
            'impuestos' => $impuestos,
            'utilidad_neta' => $utilidadNeta,
            'margen_neto' => $totalIngresos != 0 ? ($utilidadNeta / $totalIngresos) * 100 : 0,
        ];
    }

    /**
     * BALANCE DE COMPROBACIÓN - Verifica que débitos = créditos
     */
    public function balanceComprobacion(): array
    {
        $saldos = $this->obtenerSaldoCuentas();

        $cuentas = [];
        $totalDebe = 0;
        $totalHaber = 0;

        foreach ($saldos as $cuenta) {
            $debe = (float) $cuenta->total_debe;
            $haber = (float) $cuenta->total_haber;
            $saldo = $debe - $haber;

            $cuentas[] = [
                'codigo' => $cuenta->code,
                'nombre' => $cuenta->name,
                'tipo' => $cuenta->type,
                'debe' => $debe,
                'haber' => $haber,
                'saldo' => $saldo,
                'saldo_tipo' => $saldo >= 0 ? 'Deudor' : 'Acreedor',
            ];

            $totalDebe += $debe;
            $totalHaber += $haber;
        }

        // Ordena por código de cuenta
        usort($cuentas, function ($a, $b) {
            return strcmp($a['codigo'], $b['codigo']);
        });

        return [
            'tipo' => 'Balance de Comprobación',
            'fecha' => $this->fechaFin->format('Y-m-d'),
            'cuentas' => $cuentas,
            'total_debe' => $totalDebe,
            'total_haber' => $totalHaber,
            'diferencia' => abs($totalDebe - $totalHaber),
            'balanceado' => abs($totalDebe - $totalHaber) < 0.01,
        ];
    }

    /**
     * ESTADO DE FLUJOS DE EFECTIVO - Muestra movimiento de efectivo
     */
    public function flujoEfectivo(): array
    {
        // Obtiene cuentas de efectivo y bancos
        $efectivo = $this->obtenerSaldoCuentas()
            ->filter(fn ($c) => str_starts_with($c->code, '1'));

        $saldoInicial = 0; // Saldo al inicio del período
        $saldoFinal = 0;   // Saldo al cierre del período

        // Calcula flujos por categoría
        $flujoOperativo = $this->calcularFlujoOperativo();
        $flujoInversion = $this->calcularFlujoInversion();
        $flujoFinanciamiento = $this->calcularFlujoFinanciamiento();

        $flujoNeto = $flujoOperativo + $flujoInversion + $flujoFinanciamiento;

        return [
            'tipo' => 'Estado de Flujos de Efectivo',
            'fecha_inicio' => $this->fechaInicio->format('Y-m-d'),
            'fecha_fin' => $this->fechaFin->format('Y-m-d'),
            'saldo_efectivo_inicial' => $saldoInicial,
            'flujo_operativo' => [
                'monto' => $flujoOperativo,
                'descripcion' => 'Efectivo de operaciones',
            ],
            'flujo_inversion' => [
                'monto' => $flujoInversion,
                'descripcion' => 'Efectivo de inversiones',
            ],
            'flujo_financiamiento' => [
                'monto' => $flujoFinanciamiento,
                'descripcion' => 'Efectivo de financiamiento',
            ],
            'flujo_neto_periodo' => $flujoNeto,
            'saldo_efectivo_final' => $saldoFinal + $flujoNeto,
            'variacion_efectivo' => $flujoNeto,
        ];
    }

    /**
     * ESTADO DE CAMBIOS EN EL PATRIMONIO - Muestra variaciones en patrimonio
     */
    public function cambiosPatrimonio(): array
    {
        $capital = $this->obtenerSaldoCuentas()
            ->filter(fn ($c) => $c->account->type === 'equity')
            ->sum(fn ($c) => $this->calcularSaldo($c, 'equity'));

        $utilidadPeriodo = $this->obtenerUtilidadPeriodo();

        $saldoInicial = $capital; // Simplificado
        $saldoFinal = $capital + $utilidadPeriodo;

        return [
            'tipo' => 'Estado de Cambios en el Patrimonio',
            'fecha_inicio' => $this->fechaInicio->format('Y-m-d'),
            'fecha_fin' => $this->fechaFin->format('Y-m-d'),
            'saldo_inicial' => $saldoInicial,
            'aportes_capital' => 0,
            'utilidad_neta' => $utilidadPeriodo,
            'dividendos_pagados' => 0,
            'otros_cambios' => 0,
            'saldo_final' => $saldoFinal,
            'variacion_total' => $saldoFinal - $saldoInicial,
        ];
    }

    /**
     * ANÁLISIS DE RATIOS FINANCIEROS
     */
    public function ratiosFinancieros(): array
    {
        $balanceGeneral = $this->balanceGeneral();
        $estadoResultados = $this->estadoResultados();

        // Ratios de Liquidez
        $activoCirculante = $balanceGeneral['activos']['activos_circulantes'];
        $activoTotal = $balanceGeneral['total_activos'];
        $pasivoCirculante = $balanceGeneral['pasivos']['pasivos_circulantes'];
        $pasivoTotal = $balanceGeneral['pasivos']['total'];

        // Ratios de Rentabilidad
        $utilidadNeta = $estadoResultados['utilidad_neta'];
        $ingresos = $estadoResultados['ingresos']['total'];
        $patrimonio = $balanceGeneral['patrimonio']['total'];

        // Calcula sumas de activos y pasivos circulantes
        $sumActivoCirculante = array_sum(array_column($activoCirculante, 'saldo_actual'));
        $sumPasivoCirculante = array_sum(array_column($pasivoCirculante, 'saldo_actual'));

        return [
            'tipo' => 'Ratios Financieros',
            'fecha' => $this->fechaFin->format('Y-m-d'),
            'liquidez' => [
                'razon_corriente' => $sumPasivoCirculante > 0 ? $sumActivoCirculante / $sumPasivoCirculante : 0,
                'prueba_acida' => $sumPasivoCirculante > 0 ? ($sumActivoCirculante * 0.8) / $sumPasivoCirculante : 0,
                'razon_efectivo' => $sumPasivoCirculante > 0 ? 0 : 0,
            ],
            'solvencia' => [
                'razon_deuda' => $activoTotal > 0 ? $pasivoTotal / $activoTotal : 0,
                'razon_deuda_capital' => $patrimonio > 0 ? $pasivoTotal / $patrimonio : 0,
                'cobertura_interes' => 0, // Requiere más datos
            ],
            'rentabilidad' => [
                'margen_neto' => $estadoResultados['margen_neto'],
                'roa' => $activoTotal > 0 ? ($utilidadNeta / $activoTotal) * 100 : 0,
                'roe' => $patrimonio > 0 ? ($utilidadNeta / $patrimonio) * 100 : 0,
                'margen_bruto' => $estadoResultados['margen_bruto'],
            ],
            'eficiencia' => [
                'rotacion_activo' => $activoTotal > 0 ? $ingresos / $activoTotal : 0,
                'rotacion_inventario' => 0, // Requiere datos de inventario
            ],
        ];
    }

    /**
     * MÉTODOS AUXILIARES
     */

    /**
     * Calcula el saldo de una cuenta según su tipo normal
     */
    private function calcularSaldo($cuenta, string $tipoNormal): float
    {
        $debe = (float) $cuenta->total_debe;
        $haber = (float) $cuenta->total_haber;

        // Activos y gastos tienen saldo normal deudor
        if (in_array($tipoNormal, ['asset', 'expense'])) {
            return $debe - $haber;
        }

        // Pasivos, patrimonio e ingresos tienen saldo normal acreedor
        return $haber - $debe;
    }

    /**
     * Determina si una cuenta es circulante basado en el código
     */
    private function esCirulante(string $codigo): bool
    {
        // Cuentas que comienzan con 1 sin decenas específicas suelen ser circulantes
        // Ejemplo: 1100-1500 = Circulantes, 1600+ = No circulantes
        $numerico = (int) substr($codigo, 0, 4);
        return $numerico >= 1000 && $numerico < 1600;
    }

    /**
     * Obtiene la utilidad/pérdida del período
     */
    private function obtenerUtilidadPeriodo(): float
    {
        $estado = $this->estadoResultados();
        return $estado['utilidad_neta'] ?? 0;
    }

    /**
     * Calcula flujo operativo (simplificado)
     */
    private function calcularFlujoOperativo(): float
    {
        $utilidad = $this->obtenerUtilidadPeriodo();
        // En un cálculo real, agregarías depreciaciones, cambios en cuentas por cobrar, etc.
        return $utilidad;
    }

    /**
     * Calcula flujo de inversión (simplificado)
     */
    private function calcularFlujoInversion(): float
    {
        // Aquí iría la lógica para cambios en activos fijos
        return 0;
    }

    /**
     * Calcula flujo de financiamiento (simplificado)
     */
    private function calcularFlujoFinanciamiento(): float
    {
        // Aquí iría la lógica para cambios en deuda y patrimonio
        return 0;
    }

    
    private function estimarImpuestos(float $utilidad, float $tasaImpuesto = 0.25): float
    {
        return $utilidad > 0 ? $utilidad * $tasaImpuesto : 0;
    }
}