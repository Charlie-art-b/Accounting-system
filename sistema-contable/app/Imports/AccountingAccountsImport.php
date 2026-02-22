<?php

namespace App\Imports;

use Illuminate\Support\Collection;

if (interface_exists('\Maatwebsite\Excel\Concerns\ToCollection') && interface_exists('\Maatwebsite\Excel\Concerns\WithHeadingRow')) {
    class AccountingAccountsImport implements \Maatwebsite\Excel\Concerns\ToCollection, \Maatwebsite\Excel\Concerns\WithHeadingRow
    {
        public Collection $rows;

        public function __construct()
        {
            $this->rows = collect();
        }

        public function collection(Collection $rows): void
        {
            $this->rows = $rows;
        }
    }
} else {
    class AccountingAccountsImport
    {
        public Collection $rows;

        public function __construct()
        {
            $this->rows = collect();
        }

        public function collection(Collection $rows): void
        {
            $this->rows = $rows;
        }
    }
}
