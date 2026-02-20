<?php

namespace App\Services;

use App\Models\Customer;
use Carbon\Carbon;

class ClientesService
{
    protected EstadoFinancieroService $statementService;

    public function __construct(EstadoFinancieroService $statementService)
    {
        $this->statementService = $statementService;
    }

    /**
     * Get basic financial dashboard for a customer
     */
    public function dashboardCliente(int $customerId, Carbon $date = null): array
    {
        $date = $date ?? Carbon::now();

        $customer = Customer::findOrFail($customerId);

        $statement = $this->statementService
            ->setCustomer($customerId)
            ->setDates($date->copy()->startOfYear(), $date)
            ->incomeStatement();

        $balance = $this->statementService
            ->setCustomer($customerId)
            ->setDates($date->copy()->startOfYear(), $date)
            ->balanceSheet();

        return [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'identification' => $customer->identification,
                'type' => $customer->customer_type,
                'status' => $customer->status ? 'Active' : 'Inactive',
            ],

            'income_statement' => [
                'revenues' => $statement['revenues']['total'] ?? 0,
                'expenses' => $statement['expenses']['total'] ?? 0,
                'net_income' => $statement['net_income'] ?? 0,
                'net_margin' => $statement['net_margin'] ?? 0,
            ],

            'balance_sheet' => [
                'assets' => $balance['total_assets'] ?? 0,
                'liabilities' => $balance['liabilities']['total'] ?? 0,
                'equity' => $balance['equity']['total'] ?? 0,
                'equation_balanced' => $balance['equation_balanced'] ?? false,
            ],
        ];
    }
}