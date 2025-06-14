<?php

namespace App\Http\Controllers;

use App\Helpers\ClearCache;
use App\Helpers\UploadImage;
use App\Http\Requests\StoreWorkResultRequest;
use App\Models\WorkResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class WorkResultController extends Controller
{
    public function index(): JsonResponse
    {
        $page = request()->get('page', 1);
        $cacheKey = "work_results_page_{$page}";

        $workResults = Cache::remember($cacheKey, 300, function () {
            return WorkResult::all();
        });

        return response()->json($workResults);
    }

    public function store(StoreWorkResultRequest $request): JsonResponse
    {
        $image = null;

        if ($request->hasFile('before_image')) {
            $image['before_image'] = (new UploadImage)->uploadImage('work-results', $request->file('before_image'));
        }

        if ($request->hasFile('after_image')) {
            $image['after_image'] = (new UploadImage)->uploadImage('work-results', $request->file('after_image'));
        }

        $workResult = WorkResult::create([
            'title' => $request->input('title'),
            'category' => $request->input('category'),
            'before_image' => $image['before_image']['path'],
            'after_image' => $image['after_image']['path'],
            'filename_before_image' => $image['before_image']['filename'],
            'filename_after_image' => $image['after_image']['filename'],
        ]);

        $this->clearWorkResultCache();

        return response()->json($workResult);
    }

    public function destroy($id): JsonResponse
    {
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

        $this->clearWorkResultCache();

        return response()->json([
            'status' => 'success',
            'message' => 'Work result deleted successfully',
        ]);
    }

    private function clearWorkResultCache()
    {
        (new ClearCache)->clear('work_results_page_');
    }
}
