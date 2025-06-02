<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientJob extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'client',
        'date',
        'duration',
        'status',
        'price',
        'notes',
        'services',
        'team',
        'equipment'
    ];

    protected $casts = [
        'client' => 'array',
        'services' => 'array',
        'team' => 'array',
        'equipment' => 'array'
    ];
}
