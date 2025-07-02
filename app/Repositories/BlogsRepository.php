<?php

namespace App\Repositories;

use App\Helpers\ApiResponse;
use App\Helpers\ClearCache;
use App\Helpers\DeleteImage;
use App\Helpers\UploadImage;
use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Http\Requests\UUIDPageRequest;
use App\Interfaces\BlogsInterface;
use App\Models\Blog;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BlogsRepository implements BlogsInterface
{
    public function index(UUIDPageRequest $request): JsonResponse
    {
        $shop = $this->getShopByUuid($request->input('shop_uuid'));
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $page = $request->input('page', 1);
        $search = $request->input('search');
        
        // Create cache key that includes search term
        $cacheKey = "blogs_page_{$page}_shop_{$shop->id}";
        if ($search) {
            $cacheKey .= "_search_" . md5($search);
        }

        $blogs = Cache::remember($cacheKey, 300, function () use ($shop, $search) {
            $query = Blog::where('shop_id', $shop->id);
            
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('author', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
                });
            }
            
            return $query->orderBy('created_at', 'desc')->paginate(10);
        });

        return ApiResponse::success(['blogs' => $blogs]);
    }

    public function store(StoreBlogRequest $request): JsonResponse
    {
        $shop = $this->getShopByUuid($request->input('shop_uuid'));
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $image = $request->hasFile('image')
            ? (new UploadImage)->uploadImage('blogs', $request->file('image'))
            : null;

        $blog = Blog::create([
            'shop_id' => $shop->id,
            'title' => $request->input('title'),
            'slug' => Str::uuid(),
            'content' => $request->input('content'),
            'filename' => $image['filename'] ?? null,
            'image' => $image['path'] ?? null,
            'author' => $request->input('author'),
        ]);

        $this->clearBlogCache($shop);

        return ApiResponse::success(['blog' => $blog], 'Blog created successfully', 201);
    }

    public function show($shop_uuid, $slug): JsonResponse
    {
        $shop = $this->getShopByUuid($shop_uuid);
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $blog = $this->getBlogBySlug($shop, $slug);
        if (! $blog) {
            return ApiResponse::error('Blog not found', 404);
        }

        return ApiResponse::success(['blog' => $blog]);
    }

    public function update(UpdateBlogRequest $request): JsonResponse
    {
        $shop = $this->getShopByUuid($request->input('shop_uuid'));
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $blog = $this->getBlogBySlug($shop, $request->input('slug'));
        if (! $blog) {
            return ApiResponse::error('Blog not found', 404);
        }

        $updateData = [
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'author' => $request->input('author'),
        ];

        if ($request->hasFile('image')) {
            $image = (new UploadImage)->uploadImage('blogs', $request->file('image'));
            (new DeleteImage)->deleteImage('blogs/'.$blog->filename);

            $updateData['filename'] = $image['filename'];
            $updateData['image'] = $image['path'];
        }

        $blog->update($updateData);
        $this->clearBlogCache($shop);

        return ApiResponse::success(['blog' => $blog->fresh()], 'Blog updated successfully');
    }

    public function destroy($shop_uuid, $slug): JsonResponse
    {
        $shop = $this->getShopByUuid($shop_uuid);
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $blog = $this->getBlogBySlug($shop, $slug);
        if (! $blog) {
            return ApiResponse::error('Blog not found', 404);
        }

        (new DeleteImage)->deleteImage('blogs/'.$blog->filename);
        $blog->delete();
        $this->clearBlogCache($shop);

        return ApiResponse::success([], 'Blog deleted successfully');
    }

    public function clearBlogCache(Shop $shop): void
    {
        (new ClearCache)->clear('blogs_page_', $shop->id);
    }

    private function getShopByUuid(string $uuid): ?Shop
    {
        return Shop::where('uuid', $uuid)->first();
    }

    private function getBlogBySlug(Shop $shop, string $slug): ?Blog
    {
        return $shop->blogs()->where('slug', $slug)->first();
    }
}
