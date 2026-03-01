<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\AccountingAccount;

class AccountingAccountSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::first(); // solo para el primer cliente

        if (! $customer) {
            return;
        }

        $accounts = [

            // ACTIVO
            [
                'code' => '1.01',
                'name' => 'Caja',
                'type' => 'Activo',
                'classification' => 'activo_corriente',
                'normal_balance' => 'debit',
            ],
            [
                'code' => '1.02',
                'name' => 'Bancos',
                'type' => 'Activo',
                'classification' => 'activo_corriente',
                'normal_balance' => 'debit',
            ],
            [
                'code' => '1.03',
                'name' => 'Propiedad, Planta y Equipo',
                'type' => 'Activo',
                'classification' => 'activo_no_corriente',
                'normal_balance' => 'debit',
            ],

            // PASIVO
            [
                'code' => '2.01',
                'name' => 'Cuentas por Pagar',
                'type' => 'Pasivo',
                'classification' => 'pasivo_corriente',
                'normal_balance' => 'credit',
            ],
            [
                'code' => '2.02',
                'name' => 'Préstamos Bancarios',
                'type' => 'Pasivo',
                'classification' => 'pasivo_no_corriente',
                'normal_balance' => 'credit',
            ],

            // PATRIMONIO
            [
                'code' => '3.01',
                'name' => 'Capital Social',
                'type' => 'Patrimonio',
                'classification' => 'patrimonio',
                'normal_balance' => 'credit',
            ],

            // INGRESOS
            [
                'code' => '4.01',
                'name' => 'Ventas',
                'type' => 'Ingreso',
                'classification' => 'ingreso',
                'normal_balance' => 'credit',
            ],

            // GASTOS
            [
                'code' => '5.01',
                'name' => 'Costo de Ventas',
                'type' => 'Gasto',
                'classification' => 'gasto',
                'normal_balance' => 'debit',
            ],
            [
                'code' => '5.02',
                'name' => 'Gastos Administrativos',
                'type' => 'Gasto',
                'classification' => 'gasto',
                'normal_balance' => 'debit',
            ],
            [
                'code' => '5.03',
                'name' => 'Servicios Públicos',
                'type' => 'Gasto',
                'classification' => 'gasto',
                'normal_balance' => 'debit',
            ],
        ];

        foreach ($accounts as $account) {
            AccountingAccount::updateOrCreate(
                [
                    'customer_id' => $customer->id,
                    'code' => $account['code'],
                ],
                array_merge($account, [
                    'customer_id' => $customer->id,
                    'level' => 1,
                    'parent_id' => null,
                    'status' => 'Activa',
                ])
            );
        }
    }
}