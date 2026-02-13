<?php

namespace Tests\Feature\AccountPayable;

use Tests\TestCase;
use App\Models\Supplier;
use App\Models\AccountPayable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;

class AccountPayableUpdateTest extends TestCase
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
    public function update_to_paid_sets_payment_date_if_missing(): void
    {
        $supplier = $this->makeSupplier('SUP-200');

        $ap = AccountPayable::create([
            'supplier_id' => $supplier->id,
            'document_number' => 'FAC-3001',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'credit',
            'payment_period' => 30,
            'due_date' => '2026-03-03',
            'type' => 'invoice',
            'total_amount' => 1000,
            'paid_amount' => 200,
        ]);

        $this->assertEquals('partial', $ap->fresh()->status);

        $ap->paid_amount = 1000;
        $ap->payment_date = null;
        $ap->save();

        $this->assertEquals('paid', $ap->fresh()->status);
        $this->assertNotNull($ap->fresh()->payment_date);
    }

    #[Test]
    public function update_paid_amount_recalculates_status_partial_and_clears_payment_date(): void
    {
        $supplier = $this->makeSupplier('SUP-201');

        $ap = AccountPayable::create([
            'supplier_id' => $supplier->id,
            'document_number' => 'FAC-3002',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'credit',
            'payment_period' => 30,
            'due_date' => '2026-03-03',
            'type' => 'invoice',
            'total_amount' => 1000,
            'paid_amount' => 1000,
        ]);

        $this->assertEquals('paid', $ap->fresh()->status);
        $this->assertNotNull($ap->fresh()->payment_date);

        // OJO: por tus reglas, si ya estaba paid NO se permite editar.
        // Este test valida que efectivamente bloquee.
        $ap->paid_amount = 900;

        $this->expectException(ValidationException::class);
        $ap->save();
    }

    #[Test]
    public function not_allow_any_edit_if_original_status_was_paid(): void
    {
        $supplier = $this->makeSupplier('SUP-202');

        $ap = AccountPayable::create([
            'supplier_id' => $supplier->id,
            'document_number' => 'FAC-3003',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'cash',
            'payment_period' => null,
            'due_date' => '2026-02-01',
            'type' => 'invoice',
            'total_amount' => 1000,
            'paid_amount' => 1000,
        ]);

        $this->assertEquals('paid', $ap->fresh()->status);

        $ap->total_amount = 2000;

        $this->expectException(ValidationException::class);
        $ap->save();
    }

    #[Test]
    public function updating_to_voided_resets_paid_amount_and_payment_date(): void
    {
        $supplier = $this->makeSupplier('SUP-203');

        $ap = AccountPayable::create([
            'supplier_id' => $supplier->id,
            'document_number' => 'FAC-3004',
            'issue_date' => '2026-02-01',
            'payment_terms' => 'credit',
            'payment_period' => 30,
            'due_date' => '2026-03-03',
            'type' => 'invoice',
            'total_amount' => 1000,
            'paid_amount' => 200,
        ]);

        $this->assertEquals('partial', $ap->fresh()->status);

        $ap->status = 'voided';
        $ap->save();

        $this->assertEquals('voided', $ap->fresh()->status);
        $this->assertEquals(0.0, (float) $ap->fresh()->paid_amount);
        $this->assertNull($ap->fresh()->payment_date);
    }
}
