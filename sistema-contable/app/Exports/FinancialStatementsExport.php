<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FinancialStatementsExport implements WithMultipleSheets
{
    public function __construct(
        public ?Customer $customer,
        public array $data,
        public array $balance,
        public array $estadoResultados,
    ) {}

    public function sheets(): array
    {
        return [
            new Sheets\ResumenSheet($this->customer, $this->data, $this->balance, $this->estadoResultados),
            new Sheets\DetalleCuentasSheet($this->balance),
            new Sheets\GastosSheet($this->estadoResultados),
        ];
    }
}