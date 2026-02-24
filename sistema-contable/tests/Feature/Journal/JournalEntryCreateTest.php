<?php

namespace Tests\Feature\Journal;

use Tests\TestCase;
use App\Models\JournalEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class JournalEntryCreateTest extends TestCase
{
    use RefreshDatabase;
    use JournalHelpers;

    #[Test]
    public function create_journal_entry_with_defaults(): void
    {
        $customer = $this->makeCustomer('030');

        $entry = JournalEntry::create([
            'customer_id' => $customer->id,
        ])->fresh();

        $this->assertEquals('general', $entry->journal_type);
        $this->assertEquals('Operacion', $entry->entry_category);
        $this->assertEquals(0.0, (float) $entry->total_debit);
        $this->assertEquals(0.0, (float) $entry->total_credit);
    }

    #[Test]
    public function totals_are_normalized_to_non_negative_on_save(): void
    {
        $customer = $this->makeCustomer('031');

        $entry = JournalEntry::create([
            'customer_id' => $customer->id,
            'total_debit' => -100,
            'total_credit' => -50,
        ])->fresh();

        $this->assertEquals(0.0, (float) $entry->total_debit);
        $this->assertEquals(0.0, (float) $entry->total_credit);
    }
}