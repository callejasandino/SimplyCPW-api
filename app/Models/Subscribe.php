<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscribe extends Model
{
    protected $table = 'subscribe';
    protected $fillable = ['email', 'opt_in'];
    protected $hidden = ['created_at', 'updated_at'];
}
