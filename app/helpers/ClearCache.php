<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class ClearCache
{
    public function clear($key, $shop_id)
    {
        for ($page = 1; $page <= 100; $page++) {
            Cache::forget($key . "{$page}_shop_{$shop_id}");
        }

        return true;
    }
}