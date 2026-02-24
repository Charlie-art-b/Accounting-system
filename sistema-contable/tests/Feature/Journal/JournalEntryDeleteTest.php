<?php

namespace Tests\Feature\Journal;

use Tests\TestCase;
use App\Models\JournalLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class JournalEntryDeleteTest extends TestCase
{
    use RefreshDatabase;
    use JournalHelpers;

    #[Test]
    public function deleting_journal_entry_cascades_journal_lines(): void
    {
        $customer = $this->makeCustomer('050');
        $entry = $this->makeEntry($customer);

        $account = $this->makeAccount($customer, ['code' => '1.01.50']);

        $line = JournalLine::create([
            'journal_entry_id' => $entry->id,
            'accounting_account_id' => $account->id,
            'debit' => 100,
            'credit' => 0,
        ]);

        $entry->delete();

        $this->assertDatabaseMissing('journal_entries', ['id' => $entry->id]);
        $this->assertDatabaseMissing('journal_lines', ['id' => $line->id]);
    }
}