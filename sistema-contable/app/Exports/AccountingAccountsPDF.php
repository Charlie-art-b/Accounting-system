<?php

namespace App\Exports;

use App\Models\AccountingAccount;
use App\Services\PdfFallbackService;
use App\Services\SimplePdfService;

class AccountingAccountsPDF
{
    public function download()
    {
        $accounts = AccountingAccount::query()
            ->with(['customer', 'parent'])
            ->orderBy('customer_id')
            ->orderBy('code')
            ->get();

        $viewData = [
            'accounts' => $accounts,
        ];

        $pdfFacade = '\Barryvdh\DomPDF\Facade\Pdf';
        if (! class_exists($pdfFacade)) {
            $fields = [
                'customer_id',
                'code',
                'name',
                'type',
                'classification',
                'report_section',
                'normal_balance',
                'parent_code',
                'level',
                'status',
            ];

            $lines = [];
            $lines[] = 'Plan de Cuentas';
            $lines[] = 'FORMATO IMPORTABLE';
            $lines[] = implode('|', $fields);

            foreach ($accounts as $account) {
                $lines[] = implode('|', [
                    $account->customer_id,
                    str_replace('|', ' ', (string) $account->code),
                    str_replace('|', ' ', (string) $account->name),
                    str_replace('|', ' ', (string) $account->type),
                    str_replace('|', ' ', (string) $account->classification),
                    str_replace('|', ' ', (string) $account->report_section),
                    str_replace('|', ' ', (string) $account->normal_balance),
                    str_replace('|', ' ', (string) optional($account->parent)->code),
                    (string) $account->level,
                    str_replace('|', ' ', (string) $account->status),
                ]);
            }

            $pdfBytes = app(SimplePdfService::class)->fromLines($lines);
            return response()->streamDownload(function () use ($pdfBytes) {
                echo $pdfBytes;
            }, 'Plan_Cuentas_' . now()->format('Y-m-d_H-i-s') . '.pdf', [
                'Content-Type' => 'application/pdf',
            ]);
        }

        return app(PdfFallbackService::class)->download(
            view: 'exports.accounting-accounts-pdf',
            data: $viewData,
            baseFileName: 'Plan_Cuentas_' . now()->format('Y-m-d_H-i-s'),
            paper: 'a4',
            orientation: 'landscape',
        );
    }
}
