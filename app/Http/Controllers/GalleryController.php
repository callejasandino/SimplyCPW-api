<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index() {
        $perPage = request()->query('per_page', 10);
        $galleries = Gallery::paginate($perPage);
        return response()->json($galleries);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category' => 'required|string',
            'description' => 'string|nullable',
        ]);

        $imageUrl = $this->uploadImage($request->file('image'));

        $validated['image'] = $imageUrl;

        $gallery = Gallery::create($validated);
        return response()->json($gallery);
    }

    public function destroy($id) {
        $gallery = Gallery::findOrFail($id);
        $gallery->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Gallery deleted successfully'
        ]);
    }

    /**
     * Upload an image to storage/gallery and return the URL path
     *
     * @param \Illuminate\Http\UploadedFile $image
     * @return string
     */
    private function uploadImage($image)
    {
        // Generate a unique filename with timestamp and original extension
        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        
        // Store the image in the gallery directory within the public disk
        $path = $image->storeAs('gallery', $filename, 'public');
        
        // Return the URL path that can be used to access the image
        return url(Storage::url($path));
    }
}
