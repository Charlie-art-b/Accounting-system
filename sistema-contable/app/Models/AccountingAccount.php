<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingAccount extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'status',
    ];
    public function customer(){

        return $this->belongsTo(Customer::class);
    }
}
