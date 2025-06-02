<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkResult extends Model
{
    protected $fillable = [
        'title',
        'category',
        'before_image',
        'after_image',
    ];
}
