<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $casts = [
        'filters' => 'json',
        'rule' => 'json',
        'expires_at' => 'datetime'
    ];
}
