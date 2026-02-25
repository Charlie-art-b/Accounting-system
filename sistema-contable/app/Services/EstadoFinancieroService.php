<?php

namespace App\Services;

use App\Models\JournalLine;
use App\Models\JournalEntry;
use App\Models\AccountingAccount;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class EstadoFinancieroService
{
    protected ?int $customerId = null;
    protected Carbon $fechaInicio;
    protected Carbon $fechaFin;
    protected ?Carbon $fechaComparativa = null;
    protected float $tasaImpuestos = 0;

    public function __construct()
    {
        $this->fechaInicio = Carbon::now()->startOfYear();
        $this->fechaFin = Carbon::now();
    }

    public function setTasaImpuestos(float $tasa): self
    {
        $this->tasaImpuestos = $tasa;
        return $this;
    }

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

    public function setFechaComparativa(Carbon | string $fecha): self
    {
        $this->fechaComparativa = is_string($fecha) ? Carbon::parse($fecha) : $fecha;
        return $this;
    }

    private function obtenerSaldoCuentas(?Carbon $fechaHasta = null): Collection
    {
        $fecha = $fechaHasta ?? $this->fechaFin;

        $cuentas = AccountingAccount::where('status', 'Activa')
            ->when($this->customerId, function ($query) {
                return $query->where('customer_id', $this->customerId);
            })
            ->get();
        
        $resultado = collect();
        
        foreach ($cuentas as $cuenta) {
            $saldos = JournalLine::selectRaw(
                'SUM(CAST(debit AS DECIMAL(15,2))) as total_debe,
                SUM(CAST(credit AS DECIMAL(15,2))) as total_haber'
            )
            ->whereHas('journalEntry', function ($q) use ($fecha) {
                $q->whereBetween('posted_at', [
                    $this->fechaInicio->copy()->startOfDay(),
                    $fecha->copy()->endOfDay(),
                ])
                  ->when($this->customerId, function ($query) {
                      return $query->where('customer_id', $this->customerId);
                  })
                  ->where(function ($query) {
                      $query->where('is_reversal', false)
                            ->orWhereNull('is_reversal');
                  });
            })
            ->where('accounting_account_id', $cuenta->id)
            ->first();
            
            $cuenta->total_debe = (float) ($saldos?->total_debe ?? 0);
            $cuenta->total_haber = (float) ($saldos?->total_haber ?? 0);
            
            if ($cuenta->total_debe > 0 || $cuenta->total_haber > 0) {
                $resultado->push($cuenta);
            }
        }
        
        return $resultado;
    }

    
    public function balanceGeneral(): array
    {
        $saldos = $this->obtenerSaldoCuentas();
        $estado = $this->estadoResultados();

        $activosCirculantes = 0;
        $activosNoCirculantes = 0;
        $pasivosCirculantes = 0;
        $pasivosNoCirculantes = 0;
        $patrimonio = 0;
        $detalles = [];

        foreach ($saldos as $cuenta) {
            $saldo = $cuenta->normal_balance === 'debit'
                ? $cuenta->total_debe - $cuenta->total_haber
                : $cuenta->total_haber - $cuenta->total_debe;

            $detalles[] = [
                'codigo' => $cuenta->code,
                'nombre' => $cuenta->name,
                'tipo' => $cuenta->type,
                'clasificacion' => $cuenta->classification,
                'saldo' => $saldo,
            ];

            match ($cuenta->classification) {
                'activo_corriente' => $activosCirculantes += $saldo,
                'activo_no_corriente' => $activosNoCirculantes += $saldo,
                'pasivo_corriente' => $pasivosCirculantes += $saldo,
                'pasivo_no_corriente' => $pasivosNoCirculantes += $saldo,
                'patrimonio' => $patrimonio += $saldo,
                default => null,
            };
        }

        $totalActivos = $activosCirculantes + $activosNoCirculantes;
        $totalPasivos = $pasivosCirculantes + $pasivosNoCirculantes;
        $patrimonioConUtilidad = $patrimonio + $estado['utilidad_neta'];
        $totalPasivosPatrimonio = $totalPasivos + $patrimonioConUtilidad;
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
                'capital' => $patrimonio,
                'utilidad_periodo' => $estado['utilidad_neta'],
                'total' => $patrimonioConUtilidad,
            ],
            'total_activos' => $totalActivos,
            'total_pasivos_patrimonio' => $totalPasivosPatrimonio,
            'ecuacion_balanceada' => $diferencia < 0.01,
            'diferencia' => $diferencia,
            'fecha' => $this->fechaFin->format('Y-m-d'),
        ];
    }

    /**
     * ESTADO DE RESULTADOS - SIN CAMBIOS RELEVANTES
     */
    public function estadoResultados(): array
    {
        $saldos = $this->obtenerSaldoCuentas();

        $ingresos = [];
        $gastos = [];
        $totalIngresos = 0;
        $totalGastos = 0;

        foreach ($saldos as $cuenta) {
            if ($cuenta->classification === 'ingreso') {
                $saldo = $cuenta->total_haber - $cuenta->total_debe;
                $ingresos[] = [
                    'codigo' => $cuenta->code,
                    'nombre' => $cuenta->name,
                    'monto' => $saldo,
                ];
                $totalIngresos += $saldo;
            } 
            elseif ($cuenta->classification === 'gasto') {
                $saldo = $cuenta->total_debe - $cuenta->total_haber;
                $gastos[] = [
                    'codigo' => $cuenta->code,
                    'nombre' => $cuenta->name,
                    'monto' => $saldo,
                ];
                $totalGastos += $saldo;
            }
        }

        $utilidadBruta = $totalIngresos - $totalGastos;
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
     * BALANCE DE COMPROBACIÓN - SIN CAMBIOS
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
                'clasificacion' => $cuenta->classification,
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
     * RATIOS FINANCIEROS - SIN CAMBIOS
     */
    public function ratiosFinancieros(): array
    {
        $balance = $this->balanceGeneral();
        $estado = $this->estadoResultados();

        $activos = $balance['total_activos'] ?? 0;
        $pasivos = $balance['pasivos']['total'] ?? 0;
        $patrimonio = $balance['patrimonio']['total'] ?? 0;
        $utilidad = $estado['utilidad_neta'] ?? 0;
        $ingresos = $estado['ingresos']['total'] ?? 0;

        return [
            'liquidez' => [
                'razon_corriente' => $pasivos > 0 ? $activos / $pasivos : ($activos > 0 ? 999.99 : 0),
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
     * FLUJO DE EFECTIVO - SIN CAMBIOS
     */
    public function flujoEfectivo(): array
    {
        $estado = $this->estadoResultados();
        $balanceActual = $this->balanceGeneral();

        $utilidad = $estado['utilidad_neta'];

        $efectivo = 0;
        foreach ($balanceActual['detalles'] as $detalle) {
            $nombreLower = strtolower($detalle['nombre']);
            if (str_contains($nombreLower, 'caja') || 
                str_contains($nombreLower, 'banco') ||
                str_contains($nombreLower, 'efectivo')) {
                $efectivo += $detalle['saldo'];
            }
        }

        $variacionCapitalTrabajo = 0;
        $flujoOperativo = $utilidad + $variacionCapitalTrabajo;

        return [
            'utilidad_neta' => $utilidad,
            'variacion_capital_trabajo' => $variacionCapitalTrabajo,
            'flujo_operativo' => $flujoOperativo,
            'flujo_inversion' => 0,
            'flujo_financiamiento' => 0,
            'flujo_neto' => $flujoOperativo,
            'efectivo_final' => $efectivo,
            'fecha' => $this->fechaFin->format('Y-m-d'),
        ];
    }

    /**
     * CAMBIOS EN PATRIMONIO - SIN CAMBIOS
     */
    public function cambiosPatrimonio(): array
    {
        $saldos = $this->obtenerSaldoCuentas();
        $estado = $this->estadoResultados();

        $capitalInicial = 0;
        $aportes = 0;
        $retiros = 0;

        foreach ($saldos as $cuenta) {
            if ($cuenta->classification !== 'patrimonio') {
                continue;
            }

            $saldo = $cuenta->normal_balance === 'credit'
                ? $cuenta->total_haber - $cuenta->total_debe
                : $cuenta->total_debe - $cuenta->total_haber;

            if (str_contains(strtolower($cuenta->name), 'capital')) {
                $capitalInicial += $saldo;
            }
            elseif (str_contains(strtolower($cuenta->name), 'aporte')) {
                $aportes += $saldo;
            }
            elseif (str_contains(strtolower($cuenta->name), 'retiro')) {
                $retiros += $saldo;
            }
        }

        $utilidadPeriodo = $estado['utilidad_neta'];

        $patrimonioFinal =
            $capitalInicial +
            $aportes -
            $retiros +
            $utilidadPeriodo;

        return [
            'capital_inicial' => $capitalInicial,
            'aportes' => $aportes,
            'retiros' => $retiros,
            'utilidad_periodo' => $utilidadPeriodo,
            'patrimonio_final' => $patrimonioFinal,
            'cambio_neto' => $patrimonioFinal - $capitalInicial,
            'fecha' => $this->fechaFin->format('Y-m-d'),
        ];
    }
   public function estadoResultadosIntegral(): array
{
    $saldos = $this->obtenerSaldoCuentas();

    $ingresos = 0;
    $gastosOperativos = 0;
    $otrosGastos = 0;
    $depreciacion = 0;

    foreach ($saldos as $cuenta) {

        $saldo = $cuenta->normal_balance === 'credit'
            ? $cuenta->total_haber - $cuenta->total_debe
            : $cuenta->total_debe - $cuenta->total_haber;

        if ($cuenta->classification === 'ingreso') {
            $ingresos += $saldo;
        }

        if ($cuenta->classification === 'gasto') {

            $nombre = strtolower($cuenta->name);

            if (str_contains($nombre, 'depreci')) {
                $depreciacion += $saldo;
            }
            elseif (str_contains($nombre, 'otro')) {
                $otrosGastos += $saldo;
            }
            else {
                $gastosOperativos += $saldo;
            }
        }
    }

    $utilidadAntesDep = $ingresos - $gastosOperativos;
    $utilidadAntesImpuestos = $utilidadAntesDep - $depreciacion - $otrosGastos;
    $impuestos = $utilidadAntesImpuestos * $this->tasaImpuestos;
    $utilidadNeta = $utilidadAntesImpuestos - $impuestos;

    return [
        'ingresos' => $ingresos,
        'gastos_operativos' => $gastosOperativos,
        'depreciacion' => $depreciacion,
        'otros_gastos' => $otrosGastos,
        'utilidad_antes_depreciacion' => $utilidadAntesDep,
        'utilidad_antes_impuestos' => $utilidadAntesImpuestos,
        'impuestos' => $impuestos,
        'utilidad_neta' => $utilidadNeta,
        'fecha' => $this->fechaFin->format('Y-m-d'),
    ];
}
}