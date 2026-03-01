<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\AccountingAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;

class JournalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::first();
        if (! $customer) return;

        $accounts = AccountingAccount::where('customer_id', $customer->id)
            ->get()
            ->keyBy('code');

        $required = ['1.01','1.02','1.03','2.01','2.02','3.01','4.01','5.01','5.02','5.03'];

        foreach ($required as $code) {
            if (! $accounts->has($code)) {
                // Si faltan cuentas, no hacemos nada
                return;
            }
        }

        $id = fn(string $code) => $accounts[$code]->id;

        DB::transaction(function () use ($customer, $id) {

            // 1) Aporte de capital: Bancos (Debe) / Capital (Haber)
            $e1 = JournalEntry::create([
                'customer_id' => $customer->id,
                'journal_type' => 'general',
                'entry_category' => 'Operacion',
                'description' => 'Aporte de capital inicial',
                'reference' => 'CAP-001',
            ]);

            JournalLine::create([
                'journal_entry_id' => $e1->id,
                'accounting_account_id' => $id('1.02'), // Bancos
                'debit' => 5000,
                'credit' => 0,
                'description' => 'Ingreso a bancos',
            ]);

            JournalLine::create([
                'journal_entry_id' => $e1->id,
                'accounting_account_id' => $id('3.01'), // Capital
                'debit' => 0,
                'credit' => 5000,
                'description' => 'Capital social',
            ]);

            $e1->post();


            // 2) Compra de activo: PPE (Debe) / Préstamo (Haber)
            $e2 = JournalEntry::create([
                'customer_id' => $customer->id,
                'journal_type' => 'general',
                'entry_category' => 'Operacion',
                'description' => 'Compra de equipo financiado',
                'reference' => 'ACT-001',
            ]);

            JournalLine::create([
                'journal_entry_id' => $e2->id,
                'accounting_account_id' => $id('1.03'), // PPE
                'debit' => 1200,
                'credit' => 0,
                'description' => 'Compra de equipo',
            ]);

            JournalLine::create([
                'journal_entry_id' => $e2->id,
                'accounting_account_id' => $id('2.02'), // Préstamos
                'debit' => 0,
                'credit' => 1200,
                'description' => 'Financiamiento bancario',
            ]);

            $e2->post();


            // 3) Venta cobrada: Bancos (Debe) / Ventas (Haber)
            $e3 = JournalEntry::create([
                'customer_id' => $customer->id,
                'journal_type' => 'general',
                'entry_category' => 'Operacion',
                'description' => 'Venta cobrada',
                'reference' => 'VEN-001',
            ]);

            JournalLine::create([
                'journal_entry_id' => $e3->id,
                'accounting_account_id' => $id('1.02'), // Bancos
                'debit' => 2000,
                'credit' => 0,
                'description' => 'Cobro de venta',
            ]);

            JournalLine::create([
                'journal_entry_id' => $e3->id,
                'accounting_account_id' => $id('4.01'), // Ventas
                'debit' => 0,
                'credit' => 2000,
                'description' => 'Ingreso por ventas',
            ]);

            $e3->post();
        });
    }
}