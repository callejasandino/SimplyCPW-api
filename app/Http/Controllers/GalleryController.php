<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index() {
        $galleries = Gallery::paginate(10);
        return response()->json($galleries);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category' => 'required|string',
            'description' => 'string|nullable',
        ]);

        // Handle main image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $this->uploadImage($request->file('image'));
        }

        $gallery = Gallery::create($validated);
        return response()->json($gallery);
    }

    public function update(Request $request, $id) {
        $gallery = Gallery::findOrFail($id);
        
        $validated = $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category' => 'required|string',
            'description' => 'string|nullable',
        ]);

        // Handle main image replacement
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($gallery->image) {
                $oldImagePath = str_replace(url('/storage/'), '', $gallery->image);
                Storage::disk('public')->delete($oldImagePath);
            }
            
            $validated['image'] = $this->uploadImage($request->file('image'));
        }

        $gallery->update($validated);
        return response()->json($gallery);
    }

    public function destroy($id) {
        $gallery = Gallery::findOrFail($id);
        
        // Delete the associated image files if they exist
        if ($gallery->image) {
            $imagePath = str_replace(url('/storage/'), '', $gallery->image);
            Storage::disk('public')->delete($imagePath);
        }
        
        $gallery->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Gallery deleted successfully'
        ]);
    }

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
