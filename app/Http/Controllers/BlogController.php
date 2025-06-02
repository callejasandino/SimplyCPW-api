<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::paginate(10);
        return response()->json(
            [
                'blogs' => $blogs,
            ]
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'author' => 'required|string|max:255',
        ]);

        $image = null;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageUrl = $this->uploadImage($image);
        }

        $blog = Blog::create([
            'title' => $request->title,
            'slug' => Str::uuid(),
            'content' => $request->content,
            'image' => $imageUrl,
            'author' => $request->author,
        ]);

        return response()->json(
            [
                'blog' => $blog,
            ],
            201
        );
    }

    public function show($slug)
    {   
        $blog = Blog::where('slug', $slug)->first();
        
        if (!$blog) {
            return response()->json([
                'message' => 'Blog not found',
            ], 404);
        }

        return response()->json([
            'blog' => $blog,
        ], 200);
    }

    public function update(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'author' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
        ]);

        $blog = Blog::where('slug', $request->slug)->first();

        if (!$blog) {
            return response()->json([
                'message' => 'Blog not found',
            ], 404);
        }

        $updateData = [
            'title' => $request->title,
            'content' => $request->content,
            'author' => $request->author,
        ];

        // Only update image if a new one is uploaded
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageUrl = $this->uploadImage($image);
            $updateData['image'] = $imageUrl;
        }

        $blog->update($updateData);

        return response()->json([
            'blog' => $blog->fresh(),
        ], 200);
    }   

    public function destroy($slug)
    {
        $blog = Blog::where('slug', $slug)->first();
        
        if (!$blog) {
            return response()->json([
                'message' => 'Blog not found',
            ], 404);
        }   

        $blog->delete();

        return response()->json([
            'message' => 'Blog deleted successfully',
        ], 200);
    }
    
    private function uploadImage($image)
    {
        // Generate a unique filename with timestamp and original extension
        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        
        // Store the image in the gallery directory within the public disk
        $path = $image->storeAs('work-results', $filename, 'public');
        
        // Return the URL path that can be used to access the image
        return url(Storage::url($path));
    }
}
