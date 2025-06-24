<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    protected $table = 'subscribers';
    protected $fillable = ['email', 'opt_in', 'email_hash', 'options'];
    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'options' => 'array',
    ];
}
