<?php

namespace App\Filament\Widgets;

use App\Models\AccountReceivable;
use App\Models\Payment;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class WeeklyRevenueChartWidget extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 1;

    protected ?string $heading = 'Ingresos de la semana';

    protected ?string $description = 'Cobros registrados por dia';

    protected ?string $maxHeight = '180px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $startOfWeek = now()->startOfWeek(Carbon::MONDAY);

        $labels = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
        $totals = array_fill(0, 7, 0.0);

        $payments = Payment::query()
            ->where('payable_type', AccountReceivable::class)
            ->where('is_reversal', false)
            ->whereBetween('paid_at', [
                $startOfWeek->copy()->startOfDay(),
                $startOfWeek->copy()->addDays(6)->endOfDay(),
            ])
            ->get(['paid_at', 'amount']);

        foreach ($payments as $payment) {
            if (! $payment->paid_at) {
                continue;
            }

            $dayIndex = (int) Carbon::parse($payment->paid_at)->diffInDays($startOfWeek);

            if ($dayIndex >= 0 && $dayIndex <= 6) {
                $totals[$dayIndex] += (float) $payment->amount;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => $totals,
                    'borderColor' => '#d946ef',
                    'backgroundColor' => 'rgba(217, 70, 239, 0.18)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 5,
                    'pointBackgroundColor' => '#d946ef',
                    'pointBorderWidth' => 0,
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
                    'display' => false,
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(45, 48, 101, 0.3)',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}

