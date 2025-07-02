<?php

namespace App\Interfaces;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface SubscribersInterface
{
    public function subscribe(Request $request): JsonResponse;

    public function unsubscribe(Request $request): JsonResponse;
}
