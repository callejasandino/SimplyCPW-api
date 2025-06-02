<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipment;

class EquipmentController extends Controller
{
    public function index() {
        $equipments = Equipment::all();
        return response()->json([
            'equipments' => $equipments,
        ]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $equipment = Equipment::create([
            'name' => $validated['name'],
        ]);
        
        return response()->json([
            'equipment' => $equipment,
            'message' => 'Equipment created successfully',
        ]);
    }

    public function update(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $equipment = Equipment::findOrFail($request->id);
        $equipment->update($validated);
        return response()->json([
            'equipment' => $equipment,
            'message' => 'Equipment updated successfully',
        ]);
    }

    public function destroy($id) { 
        $equipment = Equipment::findOrFail($id);
        $equipment->delete();
        return response()->json([
            'message' => 'Equipment deleted successfully',
        ]);
    }
}
