<?php

namespace App\Http\Controllers;

use App\Models\BusinessHour;
use Illuminate\Http\Request;

class BusinessHoursController extends Controller
{
    public function index()
    {
        $businessHours = BusinessHour::first();

        return response()->json([
            'status' => 'success',
            'data' => $businessHours,
        ], 200);
    }

    public function store(Request $request)
    {

        $request->validate([
            'hours' => 'required|array',
        ]);

        $businessHours = BusinessHour::first();

        if ($businessHours) {
            $businessHours->update([
                'hours' => json_encode($request->input('hours')),
            ]);
        } else {
            BusinessHour::create([
                'hours' => json_encode($request->input('hours')),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Business hours created successfully',
        ], 201);
    }
}
