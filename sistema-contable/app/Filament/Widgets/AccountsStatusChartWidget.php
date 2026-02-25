<?php

namespace App\Filament\Widgets;

use App\Models\AccountPayable;
use App\Models\AccountReceivable;
use Filament\Widgets\BarChartWidget;

class AccountsStatusChartWidget extends BarChartWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = 'Estado de cuentas';

    protected ?string $description = 'Cantidad de documentos por estado (CxC y CxP)';

    protected ?string $maxHeight = '320px';

    protected function getData(): array
    {
        $receivablesByStatus = AccountReceivable::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $payablesByStatus = AccountPayable::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'datasets' => [
                [
                    'label' => 'CxC',
                    'data' => [
                        (int) ($receivablesByStatus['pending'] ?? 0),
                        (int) ($receivablesByStatus['partial'] ?? 0),
                        (int) ($receivablesByStatus['paid'] ?? 0),
                        0,
                    ],
                    'backgroundColor' => '#991FA6',
                ],
                [
                    'label' => 'CxP',
                    'data' => [
                        (int) ($payablesByStatus['pending'] ?? 0),
                        (int) ($payablesByStatus['partial'] ?? 0),
                        (int) ($payablesByStatus['paid'] ?? 0),
                        (int) ($payablesByStatus['voided'] ?? 0),
                    ],
                    'backgroundColor' => '#6F85FE',
                ],
            ],
            'labels' => ['Pendiente', 'Parcial', 'Pagado', 'Anulado'],
        ];
    }
}

