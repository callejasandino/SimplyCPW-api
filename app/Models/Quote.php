<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quote extends Model
{
    protected $fillable = [
        'shop_id',
        'firstName',
        'lastName',
        'email',
        'phone',
        'address',
        'servicesNeeded',
        'additionalInfo',
        'status',
        'agreedToTerms',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
