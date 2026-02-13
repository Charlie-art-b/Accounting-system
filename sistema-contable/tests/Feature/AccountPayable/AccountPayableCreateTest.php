<?php

namespace Tests\Feature\AccountPayable;

use Tests\TestCase;
use App\Models\Supplier;
use App\Models\AccountPayable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use PHPUnit\Framework\Attributes\Test;

class AccountPayableCreateTest extends TestCase
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
    public function create_account_payable_pending_when_paid_is_zero(): void
    {
        $supplier = $this->makeSupplier('SUP-100');

        $ap = AccountPayable::create([
            'supplier_id' => $supplier->id,
            'document_number' => 'FAC-1001',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'credit',
            'payment_period' => 30,
            'due_date' => '2026-03-03',
            'type' => 'invoice',
            'total_amount' => 1000,
            'paid_amount' => 0,
            'payment_date' => '2026-02-10', // el model la debe limpiar
            'status' => 'partial',          // el model lo recalcula
        ]);

        $this->assertEquals('pending', $ap->fresh()->status);
        $this->assertNull($ap->fresh()->payment_date);
        $this->assertEquals(1000.0, (float) $ap->fresh()->total_amount);
        $this->assertEquals(0.0, (float) $ap->fresh()->paid_amount);
        $this->assertEquals(1000.0, $ap->fresh()->pending_amount);
    }

    #[Test]
    public function create_account_payable_partial_when_paid_is_between_zero_and_total(): void
    {
        $supplier = $this->makeSupplier('SUP-101');

        $ap = AccountPayable::create([
            'supplier_id' => $supplier->id,
            'document_number' => 'FAC-1002',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'credit',
            'payment_period' => 30,
            'due_date' => '2026-03-03',
            'type' => 'invoice',
            'total_amount' => 1000,
            'paid_amount' => 200,
        ]);

        $this->assertEquals('partial', $ap->fresh()->status);
        $this->assertNull($ap->fresh()->payment_date);
        $this->assertEquals(800.0, $ap->fresh()->pending_amount);
    }

    #[Test]
    public function create_account_payable_paid_when_paid_reaches_total_and_sets_payment_date(): void
    {
        $supplier = $this->makeSupplier('SUP-102');

        $ap = AccountPayable::create([
            'supplier_id' => $supplier->id,
            'document_number' => 'FAC-1003',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'cash',
            'payment_period' => null,
            'due_date' => '2026-02-01',
            'type' => 'invoice',
            'total_amount' => 1000,
            'paid_amount' => 1000,
            'payment_date' => null,
        ]);

        $this->assertEquals('paid', $ap->fresh()->status);
        $this->assertNotNull($ap->fresh()->payment_date);
        $this->assertEquals(0.0, $ap->fresh()->pending_amount);
    }

    #[Test]
    public function paid_amount_is_capped_to_total_amount(): void
    {
        $supplier = $this->makeSupplier('SUP-103');

        $ap = AccountPayable::create([
            'supplier_id' => $supplier->id,
            'document_number' => 'FAC-1004',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'credit',
            'payment_period' => 30,
            'due_date' => '2026-03-03',
            'type' => 'invoice',
            'total_amount' => 100,
            'paid_amount' => 999,
        ]);

        $this->assertEquals(100.0, (float) $ap->fresh()->paid_amount);
        $this->assertEquals('paid', $ap->fresh()->status);
    }

    #[Test]
    public function negative_amounts_are_normalized_to_zero(): void
    {
        $supplier = $this->makeSupplier('SUP-104');

        $ap = AccountPayable::create([
            'supplier_id' => $supplier->id,
            'document_number' => 'FAC-1005',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'credit',
            'payment_period' => 30,
            'due_date' => '2026-03-03',
            'type' => 'invoice',
            'total_amount' => -50,
            'paid_amount' => -10,
        ]);

        $this->assertEquals(0.0, (float) $ap->fresh()->total_amount);
        $this->assertEquals(0.0, (float) $ap->fresh()->paid_amount);
        $this->assertEquals('pending', $ap->fresh()->status);
    }

    #[Test]
    public function voided_forces_paid_amount_zero_and_clears_payment_date(): void
    {
        $supplier = $this->makeSupplier('SUP-105');

        $ap = AccountPayable::create([
            'supplier_id' => $supplier->id,
            'document_number' => 'FAC-1006',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'credit',
            'payment_period' => 30,
            'due_date' => '2026-03-03',
            'type' => 'invoice',
            'total_amount' => 1000,
            'paid_amount' => 500,
            'payment_date' => '2026-02-10',
            'status' => 'voided',
        ]);

        $this->assertEquals('voided', $ap->fresh()->status);
        $this->assertEquals(0.0, (float) $ap->fresh()->paid_amount);
        $this->assertNull($ap->fresh()->payment_date);
    }

    #[Test]
    public function not_allow_duplicate_document_number_for_same_supplier(): void
    {
        $supplier = $this->makeSupplier('SUP-106');

        AccountPayable::create([
            'supplier_id' => $supplier->id,
            'document_number' => 'FAC-1010',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'credit',
            'payment_period' => 30,
            'due_date' => '2026-03-03',
            'type' => 'invoice',
            'total_amount' => 1000,
            'paid_amount' => 0,
        ]);

        $this->expectException(QueryException::class);

        AccountPayable::create([
            'supplier_id' => $supplier->id,
            'document_number' => 'FAC-1010',
            'issue_date' => '2026-02-02',
            'payment_terms' => 'credit',
            'payment_period' => 30,
            'due_date' => '2026-03-04',
            'type' => 'invoice',
            'total_amount' => 500,
            'paid_amount' => 0,
        ]);
    }

    #[Test]
    public function allow_same_document_number_for_different_suppliers(): void
    {
        $supplierA = $this->makeSupplier('SUP-107');
        $supplierB = $this->makeSupplier('SUP-108');

        AccountPayable::create([
            'supplier_id' => $supplierA->id,
            'document_number' => 'FAC-2000',
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
            'document_number' => 'FAC-2000',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'credit',
            'payment_period' => 30,
            'due_date' => '2026-03-03',
            'type' => 'invoice',
            'total_amount' => 1000,
            'paid_amount' => 0,
        ]);

        $this->assertDatabaseCount('accounts_payable', 2);
    }
}
