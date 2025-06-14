<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'company_name',
        'company_address',
        'company_phone',
        'company_email',
        'company_logo_filename',
        'company_logo',
        'company_description',
        'company_facebook',
        'company_instagram',
        'company_twitter',
        'company_linkedin',
        'company_youtube',
        'company_tiktok',
        'company_pinterest',
        'company_story',
        'company_mission',
        'company_vision',
        'areas_served',
        'faqs',
    ];
}
