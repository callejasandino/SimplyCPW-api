<?php

namespace App\Http\Controllers;

use App\Helpers\ClearCache;
use App\Helpers\DeleteImage;
use App\Helpers\UploadImage;
use App\Http\Requests\StoreGalleryRequest;
use App\Models\Gallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class GalleryController extends Controller
{
    public function index(): JsonResponse
    {
        $page = request()->get('page', 1);
        $cacheKey = "galleries_page_{$page}";
        $galleries = Cache::remember($cacheKey, 300, function () {
            return Gallery::paginate(10);
        });

        return response()->json($galleries);
    }

    public function store(StoreGalleryRequest $request): JsonResponse
    {
        $image = null;
        // Handle main image upload
        if ($request->hasFile('image')) {
            $image = (new UploadImage)->uploadImage('gallery', $request->file('image'));
        }

        $gallery = Gallery::create([
            'category' => $request->input('category'),
            'description' => $request->input('description'),
            'image' => $image['path'],
            'filename' => $image['filename'],
        ]);

        $this->clearGalleryCache();

        return response()->json([
            'gallery' => $gallery,
        ], 200);
    }

    public function destroy($id)
    {
        $gallery = Gallery::findOrFail($id);

        (new DeleteImage)->deleteImage('gallery/'.$gallery->filename);

        $gallery->delete();

        $this->clearGalleryCache();

        return response()->json([
            'status' => 'success',
            'message' => 'Gallery deleted successfully',
        ]);
    }

    private function clearGalleryCache()
    {
        (new ClearCache)->clear('galleries_page_');
    }
}
