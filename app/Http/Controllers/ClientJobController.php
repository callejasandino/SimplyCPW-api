<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClientJob;
use App\Models\Equipment;
use App\Models\Member;
use App\Models\Service;

class ClientJobController extends Controller
{
    public function index()
    {
        $perPage = request()->query('per_page', 10);
        $clientJobs = ClientJob::paginate($perPage);

        return response()->json([
            'status' => 'success',
            'clientJobs' => $clientJobs
        ]);
    }

    public function show($slug) {
        $clientJob = ClientJob::where('slug', $slug)->firstOrFail();
        
        return response()->json([
            'status' => 'success',
            'clientJob' => $clientJob
        ]);
    }

    public function store(Request $request) {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'client' => 'required|array',
                'date' => 'required|date',
                'duration' => 'required|integer',
                'status' => 'required|string|in:Scheduled,Pending,Confirmed,Completed,Cancelled',
                'price' => 'required|numeric',
                'notes' => 'string|nullable',
                'services' => 'array|nullable',
                'team' => 'array|nullable',
                'equipment' => 'array|nullable'
            ]);
    
            $clientJob = ClientJob::create([
                'slug' => Str::uuid(),
                'title' => $validated['title'],
                'client' => json_encode($validated['client']),
                'date' => $validated['date'],
                'duration' => $validated['duration'],
                'status' => $validated['status'],
                'price' => $validated['price'],
                'notes' => $validated['notes'],
                'services' => json_encode($validated['services']),
                'team' => json_encode($validated['team']),
                'equipment' => json_encode($validated['equipment'])
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Client job created successfully',
                'clientJob' => $clientJob
            ], 201);
        } catch (Exception $e) {
            Log::error('Error creating client job: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error creating client job'
            ], 500);
        }
    }

    public function update(Request $request) {
        try {
            $validated = $request->validate([
                'id' => 'required|integer',
                'title' => 'string|max:255|nullable',
                'client' => 'array|nullable',
                'date' => 'date|nullable',
                'duration' => 'integer|nullable',
                'status' => 'string|in:Scheduled,Pending,Confirmed,Completed,Cancelled',
                'price' => 'numeric|nullable',
                'notes' => 'string|nullable',
                'services' => 'array|nullable',
                'team' => 'array|nullable',
                'equipment' => 'array|nullable'
            ]);

            $clientJob = ClientJob::findOrFail($validated['id']);
            $clientJob->update($validated);
            return response()->json([
                'status' => 'success',
                'clientJob' => $clientJob
            ]);
        } catch (Exception $e) {
            Log::error('Error updating client job: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating client job'
            ], 500);
        }
    }

    public function destroy($id) {
        try {
            $clientJob = ClientJob::findOrFail($id);
            $clientJob->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Client job deleted successfully'
            ], 200);
        } catch (Exception $e) {
            Log::error('Error deleting client job: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting client job'
            ], 500);
        }
    }
}
