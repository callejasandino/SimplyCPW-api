<?php

namespace App\Http\Controllers;

use App\Helpers\ClearCache;
use App\Helpers\DeleteImage;
use App\Helpers\UploadImage;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        $page = request()->get('page', 1);
        $cacheKey = "services_page_{$page}";
        $services = Cache::remember($cacheKey, 300, function () {
            return Service::all();
        });

        return response()->json(['services' => $services], 200);
    }

    public function show($id): JsonResponse
    {
        $service = Service::find($id);

        return response()->json(['service' => $service], 200);
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $image = (new UploadImage)->uploadImage('services', $request->file('image'));

        $service = Service::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'image' => $image['path'],
            'filename' => $image['filename'],
            'duration' => $request->input('duration'),
        ]);

        $this->clearServiceCache();

        return response()->json(['service' => $service, 'message' => 'Service created successfully'], 201);
    }

    public function update(UpdateServiceRequest $request): JsonResponse
    {
        $service = Service::find($request->input('id'));

        if (! $service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        if ($request->hasFile('image')) {
            $image = (new UploadImage)->uploadImage('services', $request->file('image'));
            (new DeleteImage)->deleteImage('services/'.$service->filename);
        } else {
            $image = [
                'filename' => $service->filename,
                'path' => $service->image,
            ];
        }

        $service->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'image' => $image['path'],
            'filename' => $image['filename'],
            'duration' => $request->input('duration'),
        ]);

        $this->clearServiceCache();

        return response()->json(['service' => $service, 'message' => 'Service updated successfully'], 200);
    }

    public function destroy($id): JsonResponse
    {
        $service = Service::find($id);

        if (! $service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        (new DeleteImage)->deleteImage('services/'.$service->filename);

        $service->delete();

        $this->clearServiceCache();

        return response()->json(['message' => 'Service deleted successfully'], 200);
    }

    private function clearServiceCache()
    {
        (new ClearCache)->clear('services_page_');
    }
}
