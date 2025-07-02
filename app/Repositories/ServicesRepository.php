<?php

namespace App\Repositories;

use App\Helpers\ApiResponse;
use App\Helpers\ClearCache;
use App\Helpers\DeleteImage;
use App\Helpers\UploadImage;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Interfaces\ServicesInterface;
use App\Models\Service;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ServicesRepository implements ServicesInterface
{
    public function index(string $shop_uuid): JsonResponse
    {
        $page = request()->get('page', 1);
        $cacheKey = "services_page_{$page}_{$shop_uuid}";

        $services = Cache::remember($cacheKey, 300, fn () => Service::where('shop_uuid', $shop_uuid)->paginate(10)
        );

        return ApiResponse::success(['services' => $services], 'Services fetched successfully');
    }

    public function show(string $shop_uuid, string $slug): JsonResponse
    {
        $service = Service::where('shop_uuid', $shop_uuid)
            ->where('slug', $slug)
            ->first();

        if (! $service) {
            return ApiResponse::error('Service not found', 404);
        }

        return ApiResponse::success(['service' => $service], 'Service fetched successfully');
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $shop = $this->getShopByUuid($request->input('shop_uuid'));
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $image = (new UploadImage)->uploadImage('services', $request->file('image'));

        $service = Service::create([
            'shop_id' => $shop->id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'image' => $image['path'],
            'filename' => $image['filename'],
            'duration' => $request->input('duration'),
        ]);

        $this->clearServiceCache($shop);

        return ApiResponse::success(['service' => $service], 'Service created successfully', 201);
    }

    public function update(UpdateServiceRequest $request): JsonResponse
    {
        $shop = $this->getShopByUuid($request->input('shop_uuid'));
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $service = Service::where('shop_id', $shop->id)
            ->where('id', $request->input('id'))
            ->first();

        if (! $service) {
            return ApiResponse::error('Service not found', 404);
        }

        $image = $request->hasFile('image')
            ? (new UploadImage)->uploadImage('services', $request->file('image'))
            : ['filename' => $service->filename, 'path' => $service->image];

        if ($request->hasFile('image')) {
            (new DeleteImage)->deleteImage('services/'.$service->filename);
        }

        $service->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'image' => $image['path'],
            'filename' => $image['filename'],
            'duration' => $request->input('duration'),
        ]);

        $this->clearServiceCache($shop);

        return ApiResponse::success(['service' => $service], 'Service updated successfully');
    }

    public function destroy(string $shop_uuid, string $slug): JsonResponse
    {
        $shop = $this->getShopByUuid($shop_uuid);
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $service = Service::where('shop_id', $shop->id)
            ->where('slug', $slug)
            ->first();

        if (! $service) {
            return ApiResponse::error('Service not found', 404);
        }

        (new DeleteImage)->deleteImage('services/'.$service->filename);
        $service->delete();

        $this->clearServiceCache($shop);

        return ApiResponse::success([], 'Service deleted successfully');
    }

    public function clearServiceCache(Shop $shop): void
    {
        (new ClearCache)->clear('services_page_', $shop);
    }

    private function getShopByUuid(string $uuid): ?Shop
    {
        return Shop::where('uuid', $uuid)->first();
    }
}
