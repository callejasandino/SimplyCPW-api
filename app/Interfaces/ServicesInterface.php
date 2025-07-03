<?php

namespace App\Interfaces;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Requests\UUIDPageRequest;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;

interface ServicesInterface
{
    public function index(UUIDPageRequest $request): JsonResponse;

    public function store(StoreServiceRequest $request): JsonResponse;

    public function update(UpdateServiceRequest $request): JsonResponse;

    public function destroy(string $shop_uuid, string $id): JsonResponse;

    public function clearServiceCache(Shop $shop);
}
