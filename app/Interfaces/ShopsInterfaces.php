<?php

namespace App\Interfaces;

use App\Http\Requests\StoreShopRequest;
use App\Http\Requests\UpdateShopRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

interface ShopsInterfaces
{
    public function index(): JsonResponse;

    public function show(string $uuid): JsonResponse;

    public function store(StoreShopRequest $request): JsonResponse;

    public function update(UpdateShopRequest $request): JsonResponse;

    public function destroy(string $uuid): JsonResponse;

    public function clearShopCache(Auth $authUser): void;
}
