<?php

namespace Tests\Feature\AccountPayable;

use Tests\TestCase;
use App\Models\Supplier;
use App\Models\AccountPayable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use PHPUnit\Framework\Attributes\Test;

class AccountPayableDeleteTest extends TestCase
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
    public function not_allow_delete_if_status_is_not_voided(): void
    {
        $supplier = $this->makeSupplier('SUP-300');

        $ap = AccountPayable::create([
            'supplier_id' => $supplier->id,
            'document_number' => 'FAC-4001',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'credit',
            'payment_period' => 30,
            'due_date' => '2026-03-03',
            'type' => 'invoice',
            'total_amount' => 1000,
            'paid_amount' => 0,
        ]);

        $this->expectException(ValidationException::class);
        $ap->delete();
    }

    #[Test]
    public function allow_delete_if_status_is_voided(): void
    {
        $supplier = $this->makeSupplier('SUP-301');

        $ap = AccountPayable::create([
            'supplier_id' => $supplier->id,
            'document_number' => 'FAC-4002',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'credit',
            'payment_period' => 30,
            'due_date' => '2026-03-03',
            'type' => 'invoice',
            'total_amount' => 1000,
            'paid_amount' => 0,
            'status' => 'voided',
        ]);

        $ap->delete();

        $this->assertDatabaseMissing('accounts_payable', [
            'id' => $ap->id,
        ]);
    }

    #[Test]
    public function supplier_cannot_be_deleted_if_has_accounts_payable_due_to_restrict_on_delete(): void
    {
        $supplier = $this->makeSupplier('SUP-302');

        AccountPayable::create([
            'supplier_id' => $supplier->id,
            'document_number' => 'FAC-4003',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'credit',
            'payment_period' => 30,
            'due_date' => '2026-03-03',
            'type' => 'invoice',
            'total_amount' => 1000,
            'paid_amount' => 0,
        ]);

        $this->expectException(QueryException::class);
        $supplier->delete();
    }
}
