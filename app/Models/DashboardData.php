<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardData extends Model
{
    protected $fillable = [
        'stats',
        'monthlyData',
        'serviceDistribution',
        'leadSources'
    ];
}
