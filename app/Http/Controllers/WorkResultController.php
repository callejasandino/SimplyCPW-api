<?php

namespace App\Http\Controllers;

use App\Models\WorkResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WorkResultController extends Controller
{
    public function index()
    {
        $workResults = WorkResult::all();
        return response()->json($workResults);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'category' => 'required|string',
            'before_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'after_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('before_image')) {
            $validated['before_image'] = $this->uploadImage($request->file('before_image'));
        }

        if ($request->hasFile('after_image')) {
            $validated['after_image'] = $this->uploadImage($request->file('after_image'));
        }

        $workResult = WorkResult::create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'before_image' => $validated['before_image'],
            'after_image' => $validated['after_image'],
        ]);

        
        return response()->json($workResult);
    }

    public function update(Request $request) {
        $validated = $request->validate([
            'title' => 'required|string',
            'category' => 'required|string',
            'before_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'after_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $workResult = WorkResult::findOrFail($request->id);

        if ($request->hasFile('before_image')) {
            $validated['before_image'] = $this->uploadImage($request->file('before_image'));
        }

        if ($request->hasFile('after_image')) {
            $validated['after_image'] = $this->uploadImage($request->file('after_image'));
        }

        $workResult->update([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'before_image' => $validated['before_image'],
            'after_image' => $validated['after_image'],
        ]);

        return response()->json($workResult);
    }

    public function destroy($id) {
        $workResult = WorkResult::findOrFail($id);
        
        // Delete the associated image files if they exist
        if ($workResult->before_image) {
            $beforeImagePath = str_replace(url('/storage/'), '', $workResult->before_image);
            Storage::disk('public')->delete($beforeImagePath);
        }

        if ($workResult->after_image) {
            $afterImagePath = str_replace(url('/storage/'), '', $workResult->after_image);
            Storage::disk('public')->delete($afterImagePath);
        }
        
        $workResult->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Work result deleted successfully'
        ]);
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
