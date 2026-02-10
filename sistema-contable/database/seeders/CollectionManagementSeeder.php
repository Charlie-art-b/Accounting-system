<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountReceivable;
use App\Models\CollectionManagement;

class CollectionManagementSeeder extends Seeder
{
    public function run(): void
    {
        AccountReceivable::query()
            ->select(['id', 'customer_id'])
            ->chunk(200, function ($receivables) {
                foreach ($receivables as $ar) {
                    CollectionManagement::firstOrCreate(
                        ['account_receivable_id' => $ar->id],
                        [
                            'customer_id' => $ar->customer_id,
                            'next_reminder_at' => null,
                            'reminder_attempts' => 0,
                            'last_action' => null,
                            'notes' => null,
                        ]
                    );
                }
            });
    }
}
