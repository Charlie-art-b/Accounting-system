<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;

class FinancialStatsOverview extends StatsOverviewWidget
{
    public bool $ready = false;

    public array $balance = [];
    public array $estadoResultados = [];

    #[On('financial-report-generated')]
    public function loadData(array $balance, array $estadoResultados): void
    {
        $this->balance = $balance;
        $this->estadoResultados = $estadoResultados;
        $this->ready = true;
    }

    public function render(): View
    {
        // Si aún no se generó, no mostrar nada
        if (! $this->ready) {
            return view('filament.widgets.empty');
        }

        return parent::render();
    }

    protected function getStats(): array
    {
        $totalActivos = (float) ($this->balance['total_activos'] ?? 0);
        $totalPasivos = (float) ($this->balance['pasivos']['total'] ?? 0);
        $totalPatrimonio = (float) ($this->balance['patrimonio']['total'] ?? 0);

        $ingresos = (float) ($this->estadoResultados['ingresos']['total'] ?? 0);
        $gastos   = (float) ($this->estadoResultados['gastos']['total'] ?? 0);
        $utilidad = (float) ($this->estadoResultados['utilidad_neta'] ?? 0);

        return [
            Stat::make('Total Activos', number_format($totalActivos, 2)),
            Stat::make('Total Pasivos', number_format($totalPasivos, 2)),
            Stat::make('Total Patrimonio', number_format($totalPatrimonio, 2)),
           // Stat::make('Ingresos', number_format($ingresos, 2)),
           // Stat::make('Gastos', number_format($gastos, 2)),
           // Stat::make('Utilidad Neta', number_format($utilidad, 2)),
        ];
    }
}