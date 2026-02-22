<?php

namespace App\Exports;

use App\Models\AccountingAccount;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AccountingAccountsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return AccountingAccount::query()
            ->with('parent')
            ->orderBy('customer_id')
            ->orderBy('code')
            ->get();
    }

    public function headings(): array
    {
        return [
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
    }

    public function map($row): array
    {
        return [
            $row->customer_id,
            $row->code,
            $row->name,
            $row->type,
            $row->classification,
            $row->report_section,
            $row->normal_balance,
            $row->parent?->code,
            $row->level,
            $row->status,
        ];
    }
}

