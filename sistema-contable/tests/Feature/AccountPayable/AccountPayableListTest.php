<?php

namespace Tests\Feature\AccountPayable;

use Tests\TestCase;
use App\Models\Supplier;
use App\Models\AccountPayable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class AccountPayableListTest extends TestCase
{
    use RefreshDatabase;

    private function makeSupplier(string $identificacion = 'SUP-001'): Supplier
    {
        return Supplier::create([
            'tipo_proveedor' => 'persona',
            'nombre_razon_social' => 'Proveedor QA',
            'identificacion' => $identificacion,
            'correo' => strtolower($identificacion) . '@test.com',
            'telefono' => null,
            'estado' => 'activo',
        ]);
    }

    #[Test]
    public function list_all_accounts_payable(): void
    {
        $supplier = $this->makeSupplier('SUP-400');

        AccountPayable::create([
            'supplier_id' => $supplier->id,
            'document_number' => 'FAC-5001',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'credit',
            'payment_period' => 30,
            'due_date' => '2026-03-03',
            'type' => 'invoice',
            'total_amount' => 1000,
            'paid_amount' => 0,
        ]);

        AccountPayable::create([
            'supplier_id' => $supplier->id,
            'document_number' => 'FAC-5002',
            'issue_date' => '2026-02-02',
            'payment_terms' => 'cash',
            'payment_period' => null,
            'due_date' => '2026-02-02',
            'type' => 'receipt',
            'total_amount' => 500,
            'paid_amount' => 500,
        ]);

        $this->assertDatabaseCount('accounts_payable', 2);
    }

    #[Test]
    public function list_accounts_payable_by_supplier(): void
    {
        $supplierA = $this->makeSupplier('SUP-401');
        $supplierB = $this->makeSupplier('SUP-402');

        AccountPayable::create([
            'supplier_id' => $supplierA->id,
            'document_number' => 'FAC-6001',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'credit',
            'payment_period' => 30,
            'due_date' => '2026-03-03',
            'type' => 'invoice',
            'total_amount' => 1000,
            'paid_amount' => 0,
        ]);

        AccountPayable::create([
            'supplier_id' => $supplierB->id,
            'document_number' => 'FAC-6002',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'credit',
            'payment_period' => 30,
            'due_date' => '2026-03-03',
            'type' => 'invoice',
            'total_amount' => 2000,
            'paid_amount' => 0,
        ]);

        $accountsSupplierA = AccountPayable::where('supplier_id', $supplierA->id)->get();

        $this->assertCount(1, $accountsSupplierA);
        $this->assertEquals('FAC-6001', $accountsSupplierA->first()->document_number);
    }

    #[Test]
    public function pending_amount_accessor_is_calculated_correctly(): void
    {
        $supplier = $this->makeSupplier('SUP-403');

        $ap = AccountPayable::create([
            'supplier_id' => $supplier->id,
            'document_number' => 'FAC-7001',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'credit',
            'payment_period' => 30,
            'due_date' => '2026-03-03',
            'type' => 'invoice',
            'total_amount' => 1000,
            'paid_amount' => 300,
        ]);

        $this->assertEquals(700.0, $ap->pending_amount);
    }

    #[Test]
    public function can_filter_by_status(): void
    {
        $supplier = $this->makeSupplier('SUP-404');

        AccountPayable::create([
            'supplier_id' => $supplier->id,
            'document_number' => 'FAC-8001',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'credit',
            'payment_period' => 30,
            'due_date' => '2026-03-03',
            'type' => 'invoice',
            'total_amount' => 1000,
            'paid_amount' => 0, // pending
        ]);

        AccountPayable::create([
            'supplier_id' => $supplier->id,
            'document_number' => 'FAC-8002',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'credit',
            'payment_period' => 30,
            'due_date' => '2026-03-03',
            'type' => 'invoice',
            'total_amount' => 1000,
            'paid_amount' => 1000, // paid
        ]);

        $pendingAccounts = AccountPayable::where('status', 'pending')->get();

        $this->assertCount(1, $pendingAccounts);
        $this->assertEquals('FAC-8001', $pendingAccounts->first()->document_number);
    }
}
