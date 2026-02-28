<?php

namespace App\Filament\Widgets;

use App\Models\AccountPayable;
use App\Models\AccountReceivable;
use App\Models\Payment;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class RecentActivityWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.widgets.recent-activity-widget';

    protected int | string | array $columnSpan = 1;

    protected function getViewData(): array
    {
        $activities = collect()
            ->concat($this->paymentActivities())
            ->concat($this->receivableActivities())
            ->concat($this->payableActivities())
            ->sortByDesc('timestamp')
            ->take(5)
            ->values();

        return [
            'activities' => $activities,
        ];
    }

    private function paymentActivities(): Collection
    {
        return Payment::query()
            ->with('payable')
            ->where('is_reversal', false)
            ->latest('paid_at')
            ->limit(6)
            ->get()
            ->map(function (Payment $payment): array {
                $isIncome = $payment->payable_type === AccountReceivable::class;

                return [
                    'title' => $isIncome ? 'Cobro registrado' : 'Pago a proveedor registrado',
                    'time' => optional($payment->paid_at ?? $payment->created_at)->diffForHumans(),
                    'amount' => $this->money((float) $payment->amount),
                    'amountClass' => $isIncome ? 'is-positive' : 'is-neutral',
                    'prefix' => $isIncome ? '+' : '-',
                    'iconClass' => $isIncome ? 'is-emerald' : 'is-rose',
                    'timestamp' => optional($payment->paid_at ?? $payment->created_at)->timestamp ?? 0,
                ];
            });
    }

    private function receivableActivities(): Collection
    {
        return AccountReceivable::query()
            ->latest('created_at')
            ->limit(3)
            ->get()
            ->map(fn (AccountReceivable $item): array => [
                'title' => 'Factura CxC #' . ($item->invoice_number ?: $item->id),
                'time' => optional($item->created_at)->diffForHumans(),
                'amount' => $this->money((float) $item->total_amount),
                'amountClass' => 'is-neutral',
                'prefix' => '',
                'iconClass' => 'is-sky',
                'timestamp' => optional($item->created_at)->timestamp ?? 0,
            ]);
    }

    private function payableActivities(): Collection
    {
        return AccountPayable::query()
            ->latest('created_at')
            ->limit(2)
            ->get()
            ->map(fn (AccountPayable $item): array => [
                'title' => 'Cuenta por pagar #' . ($item->document_number ?: $item->id),
                'time' => optional($item->created_at)->diffForHumans(),
                'amount' => $this->money((float) $item->total_amount),
                'amountClass' => 'is-neutral',
                'prefix' => '',
                'iconClass' => 'is-violet',
                'timestamp' => optional($item->created_at)->timestamp ?? 0,
            ]);
    }

    private function money(float $value): string
    {
        return 'CRC ' . number_format($value, 0, ',', '.');
    }
}

