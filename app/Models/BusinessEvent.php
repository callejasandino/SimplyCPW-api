<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessEvent extends Model
{
    protected $fillable = [
        'shop_id',
        'title',              // e.g. "4th of July Sale"
        'slug',               // e.g. "4th-of-july-sale"
        'description',        // Long-form description
        'event_type',         // promotional, launch, announcement, etc.
        'filename',           // downloadable file (e.g. PDF, terms)
        'image',              // banner or poster
        'start_date',         // when the event starts
        'end_date',           // when it ends
        'status',             // draft, published, archived, scheduled
        'cta_link',           // e.g. a link to the promotion page
        'cta_label',          // e.g. "Shop Now", "Learn More"
        'visible',            // boolean or enum for visibility
        'discounted_services',           // discount percentage
    ];

    protected $casts = [
        'discounted_services' => 'array',
    ];

    protected $hidden = [
        'id',
        'shop_id',
        'filename',
        'created_at',
        'updated_at',
    ];
}
