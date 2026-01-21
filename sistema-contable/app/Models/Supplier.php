<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
 protected $fillable = [
        'supplier_type',
        'identification',
        'email',
        'phone',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
