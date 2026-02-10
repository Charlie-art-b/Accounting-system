<?php

namespace App\Observers;

use App\Models\AccountReceivable;
use App\Models\CollectionManagement;

class AccountReceivableObserver
{
    public function created(AccountReceivable $accountReceivable): void
    {

     \Log::info('Observer AccountReceivable creado', [
        'ar_id' => $accountReceivable->id,
        'customer_id' => $accountReceivable->customer_id,
    ]);
    
        CollectionManagement::firstOrCreate(
            ['account_receivable_id' => $accountReceivable->id],
            [
                'customer_id' => $accountReceivable->customer_id,
                'next_reminder_at' => null,
                'reminder_attempts' => 0,
                'last_action' => null,
                'notes' => null,
            ]
        );
    }
}
