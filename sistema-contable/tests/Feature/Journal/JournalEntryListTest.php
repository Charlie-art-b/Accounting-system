<?php

namespace Tests\Feature\Journal;

use Tests\TestCase;
use App\Models\JournalEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class JournalEntryListTest extends TestCase
{
    use RefreshDatabase;
    use JournalHelpers;

    #[Test]
    public function list_all_journal_entries(): void
    {
        $customer = $this->makeCustomer('040');

        JournalEntry::create(['customer_id' => $customer->id]);
        JournalEntry::create(['customer_id' => $customer->id]);

        $this->assertDatabaseCount('journal_entries', 2);
    }

    #[Test]
    public function list_journal_entries_by_customer(): void
    {
        $customerA = $this->makeCustomer('041');
        $customerB = $this->makeCustomer('042');

        JournalEntry::create(['customer_id' => $customerA->id, 'reference' => 'A-1']);
        JournalEntry::create(['customer_id' => $customerB->id, 'reference' => 'B-1']);

        $entriesA = JournalEntry::where('customer_id', $customerA->id)->get();

        $this->assertCount(1, $entriesA);
        $this->assertEquals('A-1', $entriesA->first()->reference);
    }

    #[Test]
    public function list_posted_entries_only(): void
    {
        $customer = $this->makeCustomer('043');

        JournalEntry::create(['customer_id' => $customer->id, 'posted_at' => null]);
        JournalEntry::create(['customer_id' => $customer->id, 'posted_at' => now()]);

        $posted = JournalEntry::whereNotNull('posted_at')->get();

        $this->assertCount(1, $posted);
    }
}