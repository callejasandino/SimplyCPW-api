<?php

namespace App\Http\Controllers;

use App\Models\BusinessEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BusinessEventController extends Controller
{
    public function index() {
        $businessEvents = BusinessEvent::get()->paginate(10);
        return response()->json(
            [
                'status' => 'success',
                'data' => $businessEvents
            ],
            200
        );
    }

    public function store(Request $request) {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'event_type' => 'required|string',
            'discount' => 'nullable|numeric',
        ]);

        $eventType = $request->input('event_type');
        $imageUrl = null;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageUrl = $this->uploadImage($image);
        }

        if ($eventType == 'launching') {
            $information = [
                'title' => $request->input('title'),
                'description' => $request->input('description'),
            ];

            BusinessEvent::create([
                'information' => json_encode($information),
                'event_type' => $eventType,
                'image' => $imageUrl,
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ]);
        }

        if ($eventType == 'promotional') {
            $information = [
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'discount' => $request->input('discount'),
            ];
            
            BusinessEvent::create([
                'information' => json_encode($information),
                'event_type' => $eventType,
                'image' => $imageUrl,
            ]);
        }
    }

    private function uploadImage($image)
    {
        // Generate a unique filename with timestamp and original extension
        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        
        // Store the image in the gallery directory within the public disk
        $path = $image->storeAs('business-events', $filename, 'public');
        
        // Return the URL path that can be used to access the image
        return url(Storage::url($path));
    }
}
