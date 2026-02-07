<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\AccountReceivable;

class AccountsReceivableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //clientes existentes
        $customers = Customer::take(3)->get();

        if ($customers->isEmpty()) {
            return;
        }

        $accountsReceivable = [
            [
                'invoice_number' => 'FAC-1001',
                'issue_date' => '2025-01-10',
                'due_date' => '2025-02-10',
                'description' => 'Venta de servicios contables',
                'total_amount' => 150000,
                'paid_amount' => 0,
                'status' => 'pending',
            ],
            [
                'invoice_number' => 'FAC-1002',
                'issue_date' => '2025-01-05',
                'due_date' => '2025-02-05',
                'description' => 'Venta de insumos administrativos',
                'total_amount' => 200000,
                'paid_amount' => 75000,
                'status' => 'partial',
            ],
            [
                'invoice_number' => 'FAC-1003',
                'issue_date' => '2024-12-01',
                'due_date' => '2025-01-01',
                'description' => 'Servicio cancelado en su totalidad',
                'total_amount' => 100000,
                'paid_amount' => 100000,
                'status' => 'paid',
            ],
        ];

        foreach ($customers as $i => $customer) {
            $account = $accountsReceivable[$i] ?? null;
            if (! $account) break;
            
                AccountReceivable::updateOrCreate(
                    ['invoice_number' => $account['invoice_number']],
                    array_merge($account, ['customer_id' => $customer->id])
            );
        }
    }
}
