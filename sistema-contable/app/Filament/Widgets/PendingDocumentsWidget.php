<?php

namespace App\Filament\Widgets;

use App\Models\AccountPayable;
use App\Models\AccountReceivable;
use Filament\Widgets\Widget;

class PendingDocumentsWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.widgets.pending-documents-widget';

    protected int | string | array $columnSpan = 1;

    protected function getViewData(): array
    {
        $receivablesPendingQuery = AccountReceivable::query()->whereIn('status', ['pending', 'partial']);
        $payablesPendingQuery = AccountPayable::query()->whereIn('status', ['pending', 'partial']);

        $receivablesPending = (float) $receivablesPendingQuery
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as total')
            ->value('total');

        $payablesPending = (float) $payablesPendingQuery
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as total')
            ->value('total');

        $overdueQuery = AccountReceivable::query()
            ->whereIn('status', ['pending', 'partial'])
            ->whereDate('due_date', '<', now()->toDateString());

        $overdueTotal = (float) $overdueQuery
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as total')
            ->value('total');

        $netBalance = $receivablesPending - $payablesPending;

        return [
            'rows' => [
                [
                    'label' => 'Facturas por cobrar',
                    'count' => (int) $receivablesPendingQuery->count(),
                    'amount' => $this->money($receivablesPending),
                    'class' => 'is-sky',
                ],
                [
                    'label' => 'Facturas por pagar',
                    'count' => (int) $payablesPendingQuery->count(),
                    'amount' => $this->money($payablesPending),
                    'class' => 'is-rose',
                ],
                [
                    'label' => 'Facturas vencidas',
                    'count' => (int) $overdueQuery->count(),
                    'amount' => $this->money($overdueTotal),
                    'class' => 'is-red',
                ],
            ],
            'netBalance' => $this->money(abs($netBalance)),
            'netBalanceClass' => $netBalance < 0 ? 'is-negative' : 'is-positive',
            'netBalancePrefix' => $netBalance < 0 ? '-' : '+',
        ];
    }

    private function money(float $value): string
    {
        return 'CRC ' . number_format($value, 0, ',', '.');
    }
}

