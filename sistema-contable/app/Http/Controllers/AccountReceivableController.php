<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountReceivableRequest;
use App\Models\AccountReceivable;

class AccountReceivableController extends Controller
{
    public function store(StoreAccountReceivableRequest $request)
    {
        return AccountReceivable::create([
            ...$request->validated(),
            'paid_amount' => 0,
            'status' => 'pending',
        ]);
    }
}
