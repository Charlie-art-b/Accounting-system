<?php

namespace Tests\Feature\Journal;

use App\Models\Customer;
use App\Models\AccountingAccount;
use App\Models\JournalEntry;

trait JournalHelpers
{
    protected function makeCustomer(string $seed = '001'): Customer
    {
        return Customer::create([
            'name' => "Cliente $seed",
            'first_last_name' => 'Perez',
            'second_last_name' => null,
            'id_type' => 'identification',
            'identification' => "CUST-$seed",
            'email' => "cliente$seed@test.com",
            'phone' => null,
            'address' => null,
            'customer_type' => 'individual',
            'status' => true,
            'notes' => null,
        ]);
    }

    protected function makeAccount(Customer $customer, array $overrides = []): AccountingAccount
    {
        return AccountingAccount::create(array_merge([
            'customer_id' => $customer->id,
            'code' => '1.01.01',
            'name' => 'Caja',
            'type' => 'Activo',
            'classification' => 'activo_corriente',
            'report_section' => null,
            'normal_balance' => 'debit', // requerido por migración
            'parent_id' => null,
            'level' => 1,
            'status' => 'Activa',
        ], $overrides));
    }

    protected function makeEntry(Customer $customer, array $overrides = []): JournalEntry
    {
        return JournalEntry::create(array_merge([
            'customer_id' => $customer->id,
            'journal_type' => 'general',
            'entry_category' => 'Operacion',
            'description' => 'Asiento QA',
            'reference' => 'REF-001',
        ], $overrides));
    }
}
