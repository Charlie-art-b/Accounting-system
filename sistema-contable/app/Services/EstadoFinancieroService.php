<?php

namespace App\Services;

use App\Models\JournalLine;
use App\Models\JournalEntry;
use App\Models\AccountingAccount;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Servicio de Estados Financieros
 * 
 * Genera los principales estados financieros para contadores
 */
class EstadoFinancieroService
{
    protected ?int $customerId = null;
    protected Carbon $fechaInicio;
    protected Carbon $fechaFin;
    protected ?Carbon $fechaComparativa = null;
    protected float $tasaImpuestos = 0; // 0 por defecto, sin impuestos

    public function __construct()
    {
        $this->fechaInicio = Carbon::now()->startOfYear();
        $this->fechaFin = Carbon::now();
    }

    /**
     * Establece la tasa de impuestos (ej: 0.25 para 25%)
     */
    public function setTasaImpuestos(float $tasa): self
    {
        $this->tasaImpuestos = $tasa;
        return $this;
    }

    /**
     * Establece el cliente
     */
    public function setCliente(int | Customer $cliente): self
    {
        $this->customerId = is_int($cliente) ? $cliente : $cliente->id;
        return $this;
    }

    /**
     * Establece fechas del período
     */
    public function setFechas(Carbon | string $inicio, Carbon | string $fin): self
    {
        $this->fechaInicio = is_string($inicio) ? Carbon::parse($inicio) : $inicio;
        $this->fechaFin = is_string($fin) ? Carbon::parse($fin) : $fin;
        return $this;
    }

    /**
     * Establece fecha comparativa
     */
    public function setFechaComparativa(Carbon | string $fecha): self
    {
        $this->fechaComparativa = is_string($fecha) ? Carbon::parse($fecha) : $fecha;
        return $this;
    }

    /**
     * Obtiene los saldos de las cuentas en un período
     */
    private function obtenerSaldoCuentas(?Carbon $fechaHasta = null): Collection
    {
        $fecha = $fechaHasta ?? $this->fechaFin;

        // Obtén todas las cuentas activas
        $cuentas = AccountingAccount::where('status', 'Activa')->get();
        
        $resultado = collect();
        
        foreach ($cuentas as $cuenta) {
            // Suma débitos y créditos del período
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
            ->where('accounting_account_id', $cuenta->id)
            ->first();
            
            $cuenta->total_debe = (float) ($saldos?->total_debe ?? 0);
            $cuenta->total_haber = (float) ($saldos?->total_haber ?? 0);
            
            // Solo incluye si tiene movimientos
            if ($cuenta->total_debe > 0 || $cuenta->total_haber > 0) {
                $resultado->push($cuenta);
            }
        }
        
        return $resultado;
    }

    /**
     * BALANCE GENERAL
     */
    public function balanceGeneral(): array
    {
        $saldos = $this->obtenerSaldoCuentas();

        $activosCirculantes = 0;
        $activosNoCirculantes = 0;
        $pasivosCirculantes = 0;
        $pasivosNoCirculantes = 0;
        $patrimonio = 0;
        $detalles = [];

        foreach ($saldos as $cuenta) {
            $debe = $cuenta->total_debe;
            $haber = $cuenta->total_haber;

            // Calcula saldo usando normal_balance
            $saldo = $cuenta->normal_balance === 'debit'
                ? $debe - $haber
                : $haber - $debe;

            $detalles[] = [
                'codigo' => $cuenta->code,
                'nombre' => $cuenta->name,
                'tipo' => $cuenta->type,
                'saldo' => $saldo,
            ];

            // Suma por tipo de cuenta
            if ($cuenta->type === 'Activo') {
                // Clasificación simple: códigos 1100-1199 = circulantes, 1200+ = no circulantes
                $codigo = (int)$cuenta->code;
                if ($codigo >= 1100 && $codigo < 1200) {
                    $activosCirculantes += $saldo;
                } else {
                    $activosNoCirculantes += $saldo;
                }
            } elseif ($cuenta->type === 'Pasivo') {
                // Clasificación simple: códigos 2100-2199 = circulantes, 2200+ = no circulantes
                $codigo = (int)$cuenta->code;
                if ($codigo >= 2100 && $codigo < 2200) {
                    $pasivosCirculantes += $saldo;
                } else {
                    $pasivosNoCirculantes += $saldo;
                }
            } elseif ($cuenta->type === 'Patrimonio') {
                $patrimonio += $saldo;
            }
        }

        $totalActivos = $activosCirculantes + $activosNoCirculantes;
        $totalPasivos = $pasivosCirculantes + $pasivosNoCirculantes;
        $totalPasivosPatrimonio = $totalPasivos + $patrimonio;
        $diferencia = abs($totalActivos - $totalPasivosPatrimonio);

        return [
            'detalles' => $detalles,
            'activos' => [
                'activos_circulantes' => $activosCirculantes,
                'activos_no_circulantes' => $activosNoCirculantes,
                'total' => $totalActivos,
            ],
            'pasivos' => [
                'pasivos_circulantes' => $pasivosCirculantes,
                'pasivos_no_circulantes' => $pasivosNoCirculantes,
                'total' => $totalPasivos,
            ],
            'patrimonio' => [
                'total' => $patrimonio,
            ],
            'total_activos' => $totalActivos,
            'total_pasivos_patrimonio' => $totalPasivosPatrimonio,
            'ecuacion_balanceada' => $diferencia < 0.01,
            'diferencia' => $diferencia,
            'fecha' => $this->fechaFin->format('Y-m-d'),
        ];
    }

    /**
     * ESTADO DE RESULTADOS
     */
    public function estadoResultados(): array
    {
        $saldos = $this->obtenerSaldoCuentas();

        $ingresos = [];
        $gastos = [];
        $totalIngresos = 0;
        $totalGastos = 0;

        foreach ($saldos as $cuenta) {
            $debe = $cuenta->total_debe;
            $haber = $cuenta->total_haber;

            // Los ingresos tienen saldo normal acreedor
            if ($cuenta->type === 'Ingreso') {
                $saldo = $haber - $debe;
                $ingresos[] = [
                    'codigo' => $cuenta->code,
                    'nombre' => $cuenta->name,
                    'monto' => $saldo,
                ];
                $totalIngresos += $saldo;
            } 
            // Los gastos tienen saldo normal deudor
            elseif ($cuenta->type === 'Gasto') {
                $saldo = $debe - $haber;
                $gastos[] = [
                    'codigo' => $cuenta->code,
                    'nombre' => $cuenta->name,
                    'monto' => $saldo,
                ];
                $totalGastos += $saldo;
            }
        }

        $utilidadBruta = $totalIngresos - $totalGastos;
        // Aplicar impuestos según la tasa configurada
        $impuestos = $utilidadBruta * $this->tasaImpuestos;
        $utilidadNeta = $utilidadBruta - $impuestos;
        $margenNeto = $totalIngresos > 0 ? ($utilidadNeta / $totalIngresos) * 100 : 0;

        return [
            'ingresos' => [
                'detalles' => $ingresos,
                'total' => $totalIngresos,
            ],
            'gastos' => [
                'detalles' => $gastos,
                'total' => $totalGastos,
            ],
            'utilidad_bruta' => $utilidadBruta,
            'impuestos' => $impuestos,
            'utilidad_neta' => $utilidadNeta,
            'margen_neto' => $margenNeto,
            'fecha_inicio' => $this->fechaInicio->format('Y-m-d'),
            'fecha_fin' => $this->fechaFin->format('Y-m-d'),
        ];
    }

    /**
     * BALANCE DE COMPROBACIÓN
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

            $cuentas[] = [
                'codigo' => $cuenta->code,
                'nombre' => $cuenta->name,
                'tipo' => $cuenta->type,
                'debe' => $debe,
                'haber' => $haber,
            ];

            $totalDebe += $debe;
            $totalHaber += $haber;
        }

        return [
            'cuentas' => $cuentas,
            'total_debe' => $totalDebe,
            'total_haber' => $totalHaber,
            'diferencia' => abs($totalDebe - $totalHaber),
            'balanceado' => abs($totalDebe - $totalHaber) < 0.01,
            'fecha' => $this->fechaFin->format('Y-m-d'),
        ];
    }

    /**
     * RATIOS FINANCIEROS
     */
    public function ratiosFinancieros(): array
    {
        $balance = $this->balanceGeneral();
        $estado = $this->estadoResultados();

        $activos = $balance['total_activos'] ?? 1;
        $pasivos = $balance['pasivos']['total'];
        $patrimonio = $balance['patrimonio']['total'] ?? 1;
        $utilidad = $estado['utilidad_neta'] ?? 0;
        $ingresos = $estado['ingresos']['total'] ?? 1;

        return [
            'liquidez' => [
                'razon_corriente' => $activos > 0 ? $activos / max($pasivos, 1) : 0,
            ],
            'solvencia' => [
                'razon_deuda' => $activos > 0 ? $pasivos / $activos : 0,
            ],
            'rentabilidad' => [
                'margen_neto' => $estado['margen_neto'] ?? 0,
                'roa' => $activos > 0 ? ($utilidad / $activos) * 100 : 0,
                'roe' => $patrimonio > 0 ? ($utilidad / $patrimonio) * 100 : 0,
            ],
            'eficiencia' => [
                'rotacion_activo' => $activos > 0 ? $ingresos / $activos : 0,
            ],
        ];
    }

    /**
     * FLUJO DE EFECTIVO
     */
    public function flujoEfectivo(): array
    {
        return [
            'flujo_operativo' => 0,
            'flujo_inversion' => 0,
            'flujo_financiamiento' => 0,
            'flujo_neto' => 0,
        ];
    }

    /**
     * CAMBIOS EN PATRIMONIO
     */
    public function cambiosPatrimonio(): array
    {
        return [
            'saldo_inicial' => 0,
            'aportes' => 0,
            'saldo_final' => 0,
        ];
    }
}
