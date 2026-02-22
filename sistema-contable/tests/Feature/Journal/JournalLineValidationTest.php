<?php

namespace Tests\Feature\Journal;

use Tests\TestCase;
use App\Models\JournalLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;

class JournalLineValidationTest extends TestCase
{
    use RefreshDatabase;
    use JournalHelpers;

    #[Test]
    public function not_allow_line_with_both_debit_and_credit(): void
    {
        $customer = $this->makeCustomer('010');
        $entry = $this->makeEntry($customer);
        $account = $this->makeAccount($customer, ['code' => '1.01.02']);

        $this->expectException(ValidationException::class);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'accounting_account_id' => $account->id,
            'debit' => 100,
            'credit' => 50,
        ]);
    }

    #[Test]
    public function not_allow_line_with_debit_zero_and_credit_zero(): void
    {
        $customer = $this->makeCustomer('011');
        $entry = $this->makeEntry($customer);
        $account = $this->makeAccount($customer, ['code' => '1.01.03']);

        $this->expectException(ValidationException::class);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'accounting_account_id' => $account->id,
            'debit' => 0,
            'credit' => 0,
        ]);
    }

    #[Test]
    public function not_allow_line_if_account_is_not_active(): void
    {
        $customer = $this->makeCustomer('012');
        $entry = $this->makeEntry($customer);

        $inactive = $this->makeAccount($customer, [
            'code' => '1.01.04',
            'status' => 'Inactiva',
        ]);

        $this->expectException(ValidationException::class);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'accounting_account_id' => $inactive->id,
            'debit' => 100,
            'credit' => 0,
        ]);
    }

    #[Test]
    public function not_allow_line_if_account_customer_differs_from_entry_customer(): void
    {
        $customerA = $this->makeCustomer('013');
        $customerB = $this->makeCustomer('014');

        $entryA = $this->makeEntry($customerA);
        $accountB = $this->makeAccount($customerB, ['code' => '1.01.05']);

        $this->expectException(ValidationException::class);

        JournalLine::create([
            'journal_entry_id' => $entryA->id,
            'accounting_account_id' => $accountB->id,
            'debit' => 100,
            'credit' => 0,
        ]);
    }

    #[Test]
    public function allow_valid_debit_line_and_credit_line(): void
    {
        $customer = $this->makeCustomer('015');
        $entry = $this->makeEntry($customer);

        $acctDebit = $this->makeAccount($customer, ['code' => '1.01.06', 'name' => 'Caja']);
        $acctCredit = $this->makeAccount($customer, [
            'code' => '4.01.01',
            'name' => 'Ingresos',
            'type' => 'Ingreso',
            'classification' => 'ingreso',
            'normal_balance' => 'credit',
        ]);

        $l1 = JournalLine::create([
            'journal_entry_id' => $entry->id,
            'accounting_account_id' => $acctDebit->id,
            'debit' => 150,
            'credit' => 0,
        ]);

        $l2 = JournalLine::create([
            'journal_entry_id' => $entry->id,
            'accounting_account_id' => $acctCredit->id,
            'debit' => 0,
            'credit' => 150,
        ]);

        $this->assertDatabaseHas('journal_lines', ['id' => $l1->id]);
        $this->assertDatabaseHas('journal_lines', ['id' => $l2->id]);
    }
}