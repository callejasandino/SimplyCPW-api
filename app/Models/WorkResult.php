<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkResult extends Model
{
    protected $fillable = [
        'title',
        'category',
        'filename_before_image',
        'filename_after_image',
        'before_image',
        'after_image',
    ];
}
