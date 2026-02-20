<?php

namespace App\Services;

use App\Models\Customer;
use Carbon\Carbon;

class ClientesService
{
    protected EstadoFinancieroService $estadoService;

    public function __construct(EstadoFinancieroService $estadoService)
    {
        $this->estadoService = $estadoService;
    }

    /**
     * Dashboard financiero básico de un cliente
     */
    public function dashboardCliente(int $customerId, Carbon $fecha = null): array
    {
        $fecha = $fecha ?? Carbon::now();

        $cliente = Customer::findOrFail($customerId);

        $estado = $this->estadoService
            ->setCliente($customerId)
            ->setFechas($fecha->copy()->startOfYear(), $fecha)
            ->estadoResultados();

        $balance = $this->estadoService
            ->setCliente($customerId)
            ->setFechas($fecha->copy()->startOfYear(), $fecha)
            ->balanceGeneral();

        return [
            'cliente' => [
                'id' => $cliente->id,
                'nombre' => $cliente->name,
                'identificacion' => $cliente->identification,
                'tipo' => $cliente->customer_type,
                'estado' => $cliente->status ? 'Activo' : 'Inactivo',
            ],

            'estado_resultados' => [
                'ingresos' => $estado['ingresos']['total'] ?? 0,
                'gastos' => $estado['gastos']['total'] ?? 0,
                'utilidad_neta' => $estado['utilidad_neta'] ?? 0,
                'margen_neto' => $estado['margen_neto'] ?? 0,
            ],

            'balance_general' => [
                'activos' => $balance['total_activos'] ?? 0,
                'pasivos' => $balance['pasivos']['total'] ?? 0,
                'patrimonio' => $balance['patrimonio']['total'] ?? 0,
                'ecuacion_balanceada' => $balance['ecuacion_balanceada'] ?? false,
            ],
        ];
    }
}