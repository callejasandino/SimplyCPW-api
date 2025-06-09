<?php

namespace App\Http\Controllers;

use App\Mail\JobCreatedMail;
use Illuminate\Http\Request;
use App\Models\ClientJob;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
class ClientJobController extends Controller
{
    public function index()
    {
        $clientJobs = ClientJob::paginate(10);

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
                'client' => 'required',
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

            $client = json_decode($validated['client'], true);

            $jobMail = [
                'name' => $client['name'],
                'firstName' => $client['firstName'],
                'lastName' => $client['lastName'],
                'email' => $client['email'],
                'address' => $client['address'],
                'date' => $validated['date'],
                'duration' => $validated['duration'],
                'information_link' => env('VITE_APP_NAME') . '/client-job/' . $clientJob->slug
            ];

            Mail::to(env('MAIL_FROM_ADDRESS'))->send(new JobCreatedMail($jobMail));

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

    public function destroy($slug) {
        try {
            $clientJob = ClientJob::where('slug', $slug)->firstOrFail();
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
