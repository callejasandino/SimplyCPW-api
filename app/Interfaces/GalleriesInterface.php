<?php

namespace App\Interfaces;

use App\Http\Requests\StoreGalleryRequest;
use App\Http\Requests\UUIDPageRequest;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;

interface GalleriesInterface
{
    public function index(UUIDPageRequest $request): JsonResponse;

    public function store(StoreGalleryRequest $request): JsonResponse;

    public function destroy(string $shop_uuid, int $id): JsonResponse;

    public function clearGalleryCache(Shop $shop): void;

    public function getShopByUuid(string $shop_uuid): ?Shop;
}
