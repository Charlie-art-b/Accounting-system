<?php

namespace Tests\Feature\Journal;

use Tests\TestCase;
use App\Models\JournalLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;

class JournalEntryPostTest extends TestCase
{
    use RefreshDatabase;
    use JournalHelpers;

    #[Test]
    public function post_fails_if_entry_is_not_balanced(): void
    {
        $customer = $this->makeCustomer('020');
        $entry = $this->makeEntry($customer);

        $acct1 = $this->makeAccount($customer, ['code' => '1.01.10']);
        $acct2 = $this->makeAccount($customer, [
            'code' => '4.01.10',
            'type' => 'Ingreso',
            'classification' => 'ingreso',
            'normal_balance' => 'credit',
        ]);

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
            'credit' => 50,
        ]);

        $this->expectException(ValidationException::class);
        $entry->post();
    }

    #[Test]
    public function post_sets_posted_at_and_totals_when_balanced(): void
    {
        $customer = $this->makeCustomer('021');
        $entry = $this->makeEntry($customer);

        $acctDebit = $this->makeAccount($customer, ['code' => '1.01.11']);
        $acctCredit = $this->makeAccount($customer, [
            'code' => '4.01.11',
            'type' => 'Ingreso',
            'classification' => 'ingreso',
            'normal_balance' => 'credit',
        ]);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'accounting_account_id' => $acctDebit->id,
            'debit' => 200,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'accounting_account_id' => $acctCredit->id,
            'debit' => 0,
            'credit' => 200,
        ]);

        $entry->post();

        $entry = $entry->fresh();

        $this->assertNotNull($entry->posted_at);
        $this->assertEquals(200.0, (float) $entry->total_debit);
        $this->assertEquals(200.0, (float) $entry->total_credit);
        $this->assertTrue($entry->isBalanced());
    }

    #[Test]
    public function saving_with_posted_at_requires_balanced_lines(): void
    {
        $customer = $this->makeCustomer('022');
        $entry = $this->makeEntry($customer);

        $acct1 = $this->makeAccount($customer, ['code' => '1.01.12']);
        $acct2 = $this->makeAccount($customer, [
            'code' => '4.01.12',
            'type' => 'Ingreso',
            'classification' => 'ingreso',
            'normal_balance' => 'credit',
        ]);

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
            'credit' => 90,
        ]);

        $entry->posted_at = now();

        $this->expectException(ValidationException::class);
        $entry->save();
    }
}