<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscriber extends Model
{
    protected $fillable = [
        'shop_id',
        'email',
        'opt_in',
        'email_hash',
        'options',
    ];
    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'options' => 'array',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }
}
