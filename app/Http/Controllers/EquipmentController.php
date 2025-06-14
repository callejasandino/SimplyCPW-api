<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EquipmentController extends Controller
{
    public function index()
    {
        $page = request()->get('page', 1);
        $cacheKey = "equipments_page_{$page}";
        $equipments = Cache::remember($cacheKey, 300, function () {
            return Equipment::all();
        });

        return response()->json([
            'equipments' => $equipments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $equipment = Equipment::create([
            'name' => $validated['name'],
        ]);
        $this->clearEquipmentCache();

        return response()->json([
            'equipment' => $equipment,
            'message' => 'Equipment created successfully',
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $equipment = Equipment::findOrFail($request->id);
        $equipment->update($validated);
        $this->clearEquipmentCache();

        return response()->json([
            'equipment' => $equipment,
            'message' => 'Equipment updated successfully',
        ]);
    }

    public function destroy($id)
    {
        $equipment = Equipment::findOrFail($id);
        $equipment->delete();
        $this->clearEquipmentCache();

        return response()->json([
            'message' => 'Equipment deleted successfully',
        ]);
    }

    private function clearEquipmentCache()
    {
        // Clear multiple pages of cache (assuming up to 100 pages)
        for ($page = 1; $page <= 100; $page++) {
            Cache::forget("equipments_page_{$page}");
        }
    }
}
