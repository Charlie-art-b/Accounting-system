<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountingAccount;
use App\Models\Customer;

class AccountingAccountSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::first();

        if (!$customer) {
            return;
        }

        $accounts = [
            ['code' => '1.1.01', 'name' => 'Caja', 'type' => 'Activo'],
            ['code' => '1.1.02', 'name' => 'Bancos', 'type' => 'Activo'],
            ['code' => '2.1.01', 'name' => 'Cuentas por Pagar', 'type' => 'Pasivo'],
            ['code' => '3.1.01', 'name' => 'Capital Social', 'type' => 'Patrimonio'],
            ['code' => '4.1.01', 'name' => 'Ingresos por Servicios', 'type' => 'Ingreso'],
            ['code' => '5.1.01', 'name' => 'Gastos Administrativos', 'type' => 'Gasto'],
        ];

        foreach ($accounts as $account) {

            // 🔥 Asignar balance normal automáticamente
            $normalBalance = match ($account['type']) {
                'Activo', 'Gasto' => 'debit',
                'Pasivo', 'Patrimonio', 'Ingreso' => 'credit',
                default => 'debit',
            };

            AccountingAccount::create([
                'customer_id' => $customer->id,
                'code' => $account['code'],
                'name' => $account['name'],
                'type' => $account['type'],
                'normal_balance' => $normalBalance, // 👈 ahora sí obligatorio
                'status' => 'Activa',
            ]);
        }
    }
}