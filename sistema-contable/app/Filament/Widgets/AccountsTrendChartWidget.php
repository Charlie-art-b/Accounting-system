<?php

namespace App\Filament\Widgets;

use App\Models\AccountPayable;
use App\Models\AccountReceivable;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class AccountsTrendChartWidget extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    protected ?string $heading = 'Tendencia mensual de cuentas';

    protected ?string $description = 'CxC vs CxP - ultimos 6 meses';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'bar';
    }

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
                    'label' => 'CxC',
                    'data' => array_values($receivablesByMonth->all()),
                    'backgroundColor' => '#d946ef',
                    'borderRadius' => [4, 4, 0, 0],
                    'maxBarThickness' => 28,
                ],
                [
                    'label' => 'CxP',
                    'data' => array_values($payablesByMonth->all()),
                    'backgroundColor' => '#6366f1',
                    'borderRadius' => [4, 4, 0, 0],
                    'maxBarThickness' => 28,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'color' => '#8b8fb5',
                        'font' => [
                            'size' => 11,
                        ],
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(45, 48, 101, 0.4)',
                        'borderDash' => [3, 3],
                    ],
                    'ticks' => [
                        'color' => '#8b8fb5',
                        'font' => [
                            'size' => 10,
                        ],
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'labels' => [
                        'color' => '#8b8fb5',
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'boxWidth' => 6,
                        'boxHeight' => 6,
                    ],
                ],
            ],
        ];
    }
}
