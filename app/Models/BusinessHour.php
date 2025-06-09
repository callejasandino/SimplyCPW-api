<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessHour extends Model
{
    protected $fillable = [
        'hours'
    ];

    protected $casts = [
        'hours' => 'array'
    ];
}
