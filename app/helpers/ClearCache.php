<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class ClearCache
{
    public function clear($key)
    {
        for ($page = 1; $page <= 100; $page++) {
            Cache::forget($key . "{$page}");
        }

        return true;
    }
}