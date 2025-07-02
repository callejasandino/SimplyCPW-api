<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface BusinessHoursInterface
{
    public function show($shop_uuid);

    public function store(Request $request);
}
