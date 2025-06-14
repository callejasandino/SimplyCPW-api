<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessEvent extends Model
{
    protected $fillable = [
        'information',
        'event_type',
        'image',
        'start_date',
        'end_date',
    ];
}
