<?php

namespace Tests\Feature\Journal;

use Tests\TestCase;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;

class JournalEntryUpdateTest extends TestCase
{
    use RefreshDatabase;
    use JournalHelpers;

    #[Test]
    public function update_journal_entry_fields_when_not_posted(): void
    {
        $customer = $this->makeCustomer('060');

        $entry = $this->makeEntry($customer, [
            'description' => 'Asiento original',
            'reference' => 'REF-OLD',
        ]);

        $entry->update([
            'description' => 'Asiento actualizado',
            'reference' => 'REF-NEW',
        ]);

        $entry = $entry->fresh();

        $this->assertEquals('Asiento actualizado', $entry->description);
        $this->assertEquals('REF-NEW', $entry->reference);
    }

    #[Test]
    public function cannot_force_unbalanced_posted_entry(): void
    {
        $customer = $this->makeCustomer('061');
        $entry = $this->makeEntry($customer);

        $acct1 = $this->makeAccount($customer, ['code' => '1.01.60']);
        $acct2 = $this->makeAccount($customer, [
            'code' => '4.01.60',
            'type' => 'Ingreso',
            'classification' => 'ingreso',
            'normal_balance' => 'credit',
        ]);

        // Crear asiento balanceado
        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'accounting_account_id' => $acct1->id,
            'debit' => 100,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'accounting_account_id' => $acct2->id,
            'debit' => 0,
            'credit' => 100,
        ]);

        // Postear correctamente
        $entry->post();

        // Desbalancear modificando una línea
        $line = $entry->lines()->first();
        $line->update(['debit' => 200]);

        // Intentar guardar nuevamente con posted_at activo
        $entry->posted_at = now();

        $this->expectException(ValidationException::class);
        $entry->save();
    }

    #[Test]
    public function allow_updating_description_even_if_posted(): void
    {
        $customer = $this->makeCustomer('062');
        $entry = $this->makeEntry($customer);

        $acct1 = $this->makeAccount($customer, ['code' => '1.01.61']);
        $acct2 = $this->makeAccount($customer, [
            'code' => '4.01.61',
            'type' => 'Ingreso',
            'classification' => 'ingreso',
            'normal_balance' => 'credit',
        ]);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'accounting_account_id' => $acct1->id,
            'debit' => 300,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'accounting_account_id' => $acct2->id,
            'debit' => 0,
            'credit' => 300,
        ]);

        $entry->post();

        // Actualizar solo descripción (sigue balanceado)
        $entry->update([
            'description' => 'Descripción modificada después de postear',
        ]);

        $this->assertEquals(
            'Descripción modificada después de postear',
            $entry->fresh()->description
        );
    }

    #[Test]
    public function update_reference_field_correctly(): void
    {
        $customer = $this->makeCustomer('063');

        $entry = $this->makeEntry($customer, [
            'reference' => 'REF-100',
        ]);

        $entry->update([
            'reference' => 'REF-200',
        ]);

        $this->assertEquals('REF-200', $entry->fresh()->reference);
    }

    #[Test]
    public function updating_totals_to_negative_is_normalized(): void
    {
        $customer = $this->makeCustomer('064');

        $entry = $this->makeEntry($customer);

        $entry->update([
            'total_debit' => -500,
            'total_credit' => -100,
        ]);

        $entry = $entry->fresh();

        $this->assertEquals(0.0, (float) $entry->total_debit);
        $this->assertEquals(0.0, (float) $entry->total_credit);
    }
}