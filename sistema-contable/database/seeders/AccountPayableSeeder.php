<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\AccountPayable;

class AccountPayableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // proveedores existentes
        $suppliers = Supplier::take(3)->get();

        if ($suppliers->isEmpty()) {
            return;
        }

        $accountsPayable = [
            [
                'document_number' => 'DOC-2001',
                'issue_date' => '2025-01-08',
                'payment_terms' => 'credit',
                'payment_period' => 30,
                'due_date' => '2025-02-07',
                'type' => 'invoice',
                'total_amount' => 180000,
                'paid_amount' => 0,
                'payment_date' => null,
                'status' => 'pending',
            ],
            [
                'document_number' => 'DOC-2002',
                'issue_date' => '2025-01-03',
                'payment_terms' => 'credit',
                'payment_period' => 20,
                'due_date' => '2025-01-23',
                'type' => 'receipt',
                'total_amount' => 250000,
                'paid_amount' => 100000,
                'payment_date' => null,
                'status' => 'partial',
            ],
            [
                'document_number' => 'DOC-2003',
                'issue_date' => '2024-12-15',
                'payment_terms' => 'cash',
                'payment_period' => null,
                'due_date' => '2024-12-15',
                'type' => 'debit_note',
                'total_amount' => 120000,
                'paid_amount' => 120000,
                'payment_date' => '2024-12-15',
                'status' => 'paid',
            ],
        ];

        foreach ($suppliers as $supplier) {
            foreach ($accountsPayable as $account) {
                AccountPayable::updateOrCreate(
                    [
                        'supplier_id' => $supplier->id,
                        'document_number' => $account['document_number'],
                    ],
                    array_merge($account, [
                        'supplier_id' => $supplier->id,
                    ])
                );
            }
        }
    }
}
