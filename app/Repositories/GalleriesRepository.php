<?php

namespace App\Repositories;

use App\Helpers\ApiResponse;
use App\Helpers\ClearCache;
use App\Helpers\DeleteImage;
use App\Helpers\UploadImage;
use App\Http\Requests\StoreGalleryRequest;
use App\Http\Requests\UUIDPageRequest;
use App\Interfaces\GalleriesInterface;
use App\Models\Gallery;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class GalleriesRepository implements GalleriesInterface
{
    public function index(UUIDPageRequest $request): JsonResponse
    {
        $shop = $this->getShopByUuid($request->input('shop_uuid'));
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $page = request()->get('page', 1);
        $cacheKey = "galleries_page_{$page}_shop_{$shop->id}";

        $galleries = Cache::remember($cacheKey, 300, fn () => Gallery::where('shop_id', $shop->id)->paginate(10));

        return ApiResponse::success([
            'galleries' => $galleries,
        ]);
    }

    public function store(StoreGalleryRequest $request): JsonResponse
    {
        $shop = $this->getShopByUuid($request->input('shop_uuid'));
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $image = null;
        if ($request->hasFile('image')) {
            $image = (new UploadImage)->uploadImage('gallery', $request->file('image'));
        }

        $gallery = Gallery::create([
            'shop_id' => $shop->id,
            'description' => $request->input('description'),
            'image' => $image['path'] ?? null,
            'filename' => $image['filename'] ?? null,
        ]);

        $this->clearGalleryCache($shop);

        return ApiResponse::success([
            'gallery' => $gallery,
        ], 'Gallery created successfully', 201);
    }

    public function destroy(string $shop_uuid, int $id): JsonResponse
    {
        $shop = $this->getShopByUuid($shop_uuid);
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $gallery = Gallery::where('shop_id', $shop->id)->find($id);
        if (! $gallery) {
            return ApiResponse::error('Gallery not found', 404);
        }

        (new DeleteImage)->deleteImage('gallery/'.$gallery->filename);
        $gallery->delete();

        $this->clearGalleryCache($shop);

        return ApiResponse::success([], 'Gallery deleted successfully');
    }

    public function clearGalleryCache(Shop $shop): void
    {
        (new ClearCache)->clear('galleries_page_', $shop->id);
    }

    public function getShopByUuid(string $shop_uuid): ?Shop
    {
        return Shop::where('uuid', $shop_uuid)->first();
    }
}
