<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Shop extends Model
{
    protected $fillable = [
        'user_id',
        'uuid',
        'name',
        'address',
        'phone',
        'email',
        'filename',
        'path',
        'facebook',
        'instagram',
        'twitter',
        'linkedin',
        'youtube',
        'tiktok',
        'pinterest',
        'story',
        'mission',
        'vision',
        'faqs',
        'terms_and_conditions',
        'privacy_policy',
    ];

    protected $casts = [
        'faqs' => 'array',
    ];

    protected $hidden = [
        'user_id',
        'created_at',
        'updated_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'shop_id', 'id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'shop_id', 'id');
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class, 'shop_id', 'id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'shop_id', 'id');
    }

    public function businessHours(): HasOne
    {
        return $this->hasOne(BusinessHour::class, 'shop_id', 'id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'shop_id', 'id');
    }

    public function subscribers(): HasMany
    {
        return $this->hasMany(Subscriber::class, 'shop_id', 'id');
    }
}
