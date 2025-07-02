<?php

namespace App\Interfaces;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;

interface ServicesInterface
{
    public function index(string $shop_uuid): JsonResponse;

    public function show(string $shop_uuid, string $slug): JsonResponse;

    public function store(StoreServiceRequest $request): JsonResponse;

    public function update(UpdateServiceRequest $request): JsonResponse;

    public function destroy(string $shop_uuid, string $slug): JsonResponse;

    public function clearServiceCache(Shop $shop);
}
