<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'phone',
        'address',
        'servicesNeeded',
        'additionalInfo',
        'status',
        'agreedToTerms'
    ];
}
