<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'first_last_name',
        'second_last_name',
        'id_type',
        'identification',
        'email',
        'phone',
        'address',
        'customer_type',
        'status',
        'notes',
    ];

    protected $casts = [
        'customer_type' => 'string',
        'status' => 'boolean',
    ];
}
