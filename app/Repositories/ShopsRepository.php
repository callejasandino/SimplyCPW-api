<?php

namespace App\Repositories;

use App\Helpers\ApiResponse;
use App\Helpers\ClearCache;
use App\Helpers\DeleteImage;
use App\Helpers\UploadImage;
use App\Http\Requests\StoreShopRequest;
use App\Http\Requests\UpdateShopRequest;
use App\Interfaces\ShopsInterfaces;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ShopsRepository implements ShopsInterfaces
{
    public function index(): JsonResponse
    {
        $authUser = Auth::user();

        $page = request()->get('page', 1);
        $cacheKey = "shops_page_{$page}_shop_{$authUser->id}";
        $shops = Cache::remember($cacheKey, 300, function () use ($authUser) {
            return Shop::where('user_id', $authUser->id)->paginate(10);
        });

        return response()->json([
            'shops' => $shops,
        ]);
    }

    public function show($uuid): JsonResponse
    {
        $authUser = Auth::user();

        $shop = Shop::where('user_id', $authUser->id)->where('uuid', $uuid)->first();

        return ApiResponse::success(['shop' => $shop], 'Shop retrieved successfully', 200);
    }

    public function store(StoreShopRequest $request): JsonResponse
    {
        $authUser = Auth::user();

        $image = null;

        // Handle main image upload
        if ($request->hasFile('logo')) {
            $image = (new UploadImage)->uploadImage('shops', $request->file('logo'));
        }

        $shop = Shop::create([
            'user_id' => $authUser->id,
            'uuid' => Str::uuid(),
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'filename' => $image['filename'],
            'path' => $image['path'],
        ]);

        $this->clearShopCache($authUser);

        return ApiResponse::success(['shop' => $shop], 'Shop created successfully', 201);
    }

    public function update(UpdateShopRequest $request): JsonResponse
    {
        $authUser = Auth::user();

        $shop = Shop::where('user_id', $authUser->id)->where('uuid', $request->uuid)->first();

        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $image = null;

        if ($request->hasFile('logo')) {
            $image = (new UploadImage)->uploadImage('shops', $request->file('logo'));
        } else {
            $image['filename'] = $shop->filename;
            $image['path'] = $shop->path;
        }

        $shop->update([
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'filename' => $image['filename'],
            'path' => $image['path'],
            'facebook' => $request->input('facebook'),
            'instagram' => $request->input('instagram'),
            'twitter' => $request->input('twitter'),
            'linkedin' => $request->input('linkedin'),
            'youtube' => $request->input('youtube'),
            'tiktok' => $request->input('tiktok'),
            'pinterest' => $request->input('pinterest'),
            'story' => $request->input('story'),
            'mission' => $request->input('mission'),
            'vision' => $request->input('vision'),
            'faqs' => $request->input('faqs'),
            'terms_and_conditions' => $request->input('terms_and_conditions'),
            'privacy_policy' => $request->input('privacy_policy'),
        ]);

        $this->clearShopCache($authUser);

        return ApiResponse::success(['shop' => $shop], 'Shop updated successfully', 200);
    }

    public function destroy($uuid): JsonResponse
    {
        $authUser = Auth::user();

        $shop = Shop::where('user_id', $authUser->id)->where('uuid', $uuid)->first();

        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        (new DeleteImage)->deleteImage('shops/'.$shop->filename);

        $shop->delete();

        $this->clearShopCache($authUser);

        return ApiResponse::success([], 'Shop deleted successfully', 200);
    }

    public function clearShopCache($authUser): void
    {
        (new ClearCache)->clear('shops_page_', $authUser->id);
    }
}
