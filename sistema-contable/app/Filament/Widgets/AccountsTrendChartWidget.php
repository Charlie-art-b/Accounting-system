<?php

namespace App\Filament\Widgets;

use App\Models\AccountPayable;
use App\Models\AccountReceivable;
use Carbon\Carbon;
use Filament\Widgets\LineChartWidget;

class AccountsTrendChartWidget extends LineChartWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = 'Tendencia mensual de cuentas';

    protected ?string $description = 'Montos emitidos de CxC vs CxP (ultimos 6 meses)';

    protected ?string $maxHeight = '320px';

    protected function getData(): array
    {
        $start = now()->startOfMonth()->subMonths(5);
        $months = collect(range(0, 5))
            ->map(fn (int $offset): Carbon => $start->copy()->addMonths($offset));

        $labels = $months
            ->map(fn (Carbon $month): string => $month->translatedFormat('M Y'))
            ->values()
            ->all();

        $initialValues = $months
            ->mapWithKeys(fn (Carbon $month): array => [$month->format('Y-m') => 0.0]);

        $receivablesByMonth = $initialValues->merge(
            AccountReceivable::query()
                ->whereDate('issue_date', '>=', $start->toDateString())
                ->whereNotNull('issue_date')
                ->get(['issue_date', 'total_amount'])
                ->groupBy(fn (AccountReceivable $item): string => Carbon::parse($item->issue_date)->format('Y-m'))
                ->map(fn ($items): float => (float) $items->sum('total_amount'))
        );

        $payablesByMonth = $initialValues->merge(
            AccountPayable::query()
                ->whereDate('issue_date', '>=', $start->toDateString())
                ->whereNotNull('issue_date')
                ->get(['issue_date', 'total_amount'])
                ->groupBy(fn (AccountPayable $item): string => Carbon::parse($item->issue_date)->format('Y-m'))
                ->map(fn ($items): float => (float) $items->sum('total_amount'))
        );

        return [
            'datasets' => [
                [
                    'label' => 'Cuentas por cobrar',
                    'data' => array_values($receivablesByMonth->all()),
                    'borderColor' => '#991FA6',
                    'backgroundColor' => 'rgba(153, 31, 166, 0.22)',
                    'tension' => 0.35,
                    'fill' => true,
                ],
                [
                    'label' => 'Cuentas por pagar',
                    'data' => array_values($payablesByMonth->all()),
                    'borderColor' => '#2441E1',
                    'backgroundColor' => 'rgba(36, 65, 225, 0.22)',
                    'tension' => 0.35,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
