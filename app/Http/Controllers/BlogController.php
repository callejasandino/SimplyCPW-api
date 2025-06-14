<?php

namespace App\Http\Controllers;

use App\Helpers\ClearCache;
use App\Helpers\DeleteImage;
use App\Helpers\UploadImage;
use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(): JsonResponse
    {
        $page = request()->get('page', 1);
        $cacheKey = "blogs_page_{$page}";
        $blogs = Cache::remember($cacheKey, 300, function () {
            return Blog::paginate(10);
        });

        return response()->json(
            [
                'blogs' => $blogs,
            ]
        );
    }

    public function store(StoreBlogRequest $request): JsonResponse
    {
        $image = null;

        if ($request->hasFile('image')) {
            $image = (new UploadImage)->uploadImage('blogs', $request->file('image'));
        }

        $blog = Blog::create([
            'title' => $request->input('title'),
            'slug' => Str::uuid(),
            'content' => $request->input('content'),
            'filename' => $image['filename'],
            'image' => $image['path'],
            'author' => $request->input('author'),
        ]);

        $this->clearBlogCache();

        return response()->json(
            [
                'blog' => $blog,
            ],
            201
        );
    }

    public function show($slug): JsonResponse
    {
        $blog = Blog::where('slug', $slug)->first();

        if (! $blog) {
            return response()->json([
                'message' => 'Blog not found',
            ], 404);
        }

        return response()->json([
            'blog' => $blog,
        ], 200);
    }

    public function update(UpdateBlogRequest $request): JsonResponse
    {
        $blog = Blog::where('slug', $request->slug)->first();

        if (! $blog) {
            return response()->json([
                'message' => 'Blog not found',
            ], 404);
        }

        $updateData = [
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'author' => $request->input('author'),
        ];

        // Only update image if a new one is uploaded
        if ($request->hasFile('image')) {
            $image = (new UploadImage)->uploadImage('blogs', $request->file('image'));

            (new DeleteImage)->deleteImage('blogs/'.$blog->filename);

            $updateData['filename'] = $image['filename'];
            $updateData['image'] = $image['path'];
        }

        $blog->update($updateData);

        $this->clearBlogCache();

        return response()->json([
            'blog' => $blog->fresh(),
        ], 200);
    }

    public function destroy($slug): JsonResponse
    {
        $blog = Blog::where('slug', $slug)->first();

        if (! $blog) {
            return response()->json([
                'message' => 'Blog not found',
            ], 404);
        }

        (new DeleteImage)->deleteImage('blogs/'.$blog->filename);

        $blog->delete();

        $this->clearBlogCache();

        return response()->json([
            'message' => 'Blog deleted successfully',
        ], 200);
    }

    private function clearBlogCache()
    {
        (new ClearCache)->clear('blogs_page_');
    }
}
