<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    /**
     * Display a listing of services.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $services = Service::all();
        return response()->json(['services' => $services], 200);
    }

    public function show($id)
    {
        $service = Service::find($id);
        return response()->json(['service' => $service], 200);
    }

    /**
     * Store a newly created service.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'image' => 'nullable|sometimes',
            'duration' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $image = $request->file('image');

        // Generate a unique filename with timestamp and original extension
        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
    
        // Store the image in the gallery directory within the public disk
        $path = $image->storeAs('services', $filename, 'public');
        
        $imageUrl = url(Storage::url($path));

        $service = Service::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'image' => $imageUrl,
            'duration' => $request->input('duration'),
        ]);
        
        return response()->json(['service' => $service, 'message' => 'Service created successfully'], 201);
    }

    /**
     * Update the specified service.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $service = Service::find($request->input('id'));

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'image' => 'nullable|sometimes',
            'duration' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('image')) {
            // Handle file upload
            $image = $request->file('image');

            // Generate a unique filename with timestamp and original extension
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        
            // Store the image in the gallery directory within the public disk
            $path = $image->storeAs('services', $filename, 'public');
            
            $imageUrl = url(Storage::url($path));
        } elseif ($request->has('image') && is_string($request->input('image'))) {
            // Handle string URL
            $imageUrl = $request->input('image');
        } else {
            // Keep existing image
            $imageUrl = $service->image;
        }

        $service->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'image' => $imageUrl,
            'duration' => $request->input('duration'),
        ]);

        return response()->json(['service' => $service, 'message' => 'Service updated successfully'], 200);
    }

    /**
     * Remove the specified service.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $service = Service::find($id);
        
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }
        
        $service->delete();
        return response()->json(['message' => 'Service deleted successfully'], 200);
    }
}
