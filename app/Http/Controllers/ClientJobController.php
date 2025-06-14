<?php

namespace App\Http\Controllers;

use App\Mail\JobCreatedMail;
use App\Models\ClientJob;
use App\Models\Equipment;
use App\Models\Member;
use App\Models\Service;
use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ClientJobController extends Controller
{
    public function index()
    {
        $page = request()->get('page', 1);

        $cacheKey = "client_jobs_page_{$page}";

        $clientJobs = Cache::remember($cacheKey, 300, function () {
            return ClientJob::paginate(10);
        });

        return response()->json([
            'status' => 'success',
            'clientJobs' => $clientJobs,
        ]);
    }

    public function show($slug)
    {
        $clientJob = ClientJob::where('slug', $slug)->firstOrFail()->makeHidden(['id', 'slug', 'created_at', 'updated_at', 'client.id', 'client.phone']);

        $settings = Setting::select([
            'company_facebook',
            'company_instagram',
            'company_twitter',
            'company_linkedin',
            'company_youtube',
            'company_tiktok',
            'company_pinterest',
            'company_name',
            'company_address',
            'company_phone',
            'company_email',
            'company_logo',
        ])->first();

        $services = $clientJob->services;
        $serviceNames = [];
        $equipementNames = [];
        $memberNames = [];

        $equipements = $clientJob->equipment;

        if ($equipements) {
            foreach ($equipements as $equipement) {
                $equipement = Equipment::where('id', $equipement)->first();
                if ($equipement) {
                    $equipementNames[] = $equipement->name;
                }
            }
        }

        if ($services) {
            foreach ($services as $service) {
                $service = Service::where('id', $service)->first();
                $serviceNames[] = $service->name;
            }
        }

        $teams = $clientJob->team;
        if ($teams) {
            foreach ($teams as $team) {
                $member = Member::where('id', $team)->first();
                if ($member) {
                    $memberNames[] = [
                        'name' => $member->name,
                        'image' => $member->image,
                        'contact_number' => $member->contact_number,
                    ];
                }
            }
        }

        $clientJob->services = $serviceNames;
        $clientJob->equipment = $equipementNames;
        $clientJob->team = $memberNames;

        return response()->json([
            'status' => 'success',
            'clientJob' => $clientJob,
            'settings' => $settings,
        ]);
    }

    public function store(Request $request)
    {
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
                'equipment' => 'array|nullable',
            ]);

            $clientJob = ClientJob::create([
                'slug' => Str::uuid(),
                'title' => $validated['title'],
                'client' => $validated['client'],
                'date' => $validated['date'],
                'duration' => $validated['duration'],
                'status' => $validated['status'],
                'price' => $validated['price'],
                'notes' => $validated['notes'],
                'services' => $validated['services'],
                'team' => $validated['team'],
                'equipment' => $validated['equipment'],
            ]);

            $client = $clientJob->client;

            $jobMail = [
                'name' => $client['firstName'].' '.$client['lastName'],
                'email' => $client['email'] ?? null,
                'address' => $client['address'],
                'date' => $validated['date'],
                'duration' => $validated['duration'],
                'information_link' => env('VITE_APP_NAME').'/job/'.$clientJob->slug,
            ];

            Mail::to($jobMail['email'])->send(new JobCreatedMail($jobMail));

            $this->clearClientJobCache();

            return response()->json([
                'status' => 'success',
                'message' => 'Client job created successfully',
                'clientJob' => $clientJob,
            ], 201);
        } catch (Exception $e) {
            Log::error('Error creating client job: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error creating client job',
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'uuid' => 'required|string|max:255',
                'title' => 'string|max:255|nullable',
                'client' => 'array|nullable',
                'date' => 'date|nullable',
                'duration' => 'integer|nullable',
                'status' => 'string|in:Scheduled,Pending,Confirmed,Completed,Cancelled',
                'price' => 'numeric|nullable',
                'notes' => 'string|nullable',
                'services' => 'array|nullable',
                'team' => 'array|nullable',
                'equipment' => 'array|nullable',
            ]);

            $clientJob = ClientJob::where('slug', $validated['uuid'])->firstOrFail();
            $clientJob->update($validated);

            $this->clearClientJobCache();

            return response()->json([
                'status' => 'success',
                'clientJob' => $clientJob,
            ]);
        } catch (Exception $e) {
            Log::error('Error updating client job: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error updating client job',
            ], 500);
        }
    }

    public function destroy($slug)
    {
        try {
            $clientJob = ClientJob::where('slug', $slug)->firstOrFail();
            $clientJob->delete();

            $this->clearClientJobCache();

            return response()->json([
                'status' => 'success',
                'message' => 'Client job deleted successfully',
            ], 200);
        } catch (Exception $e) {
            Log::error('Error deleting client job: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting client job',
            ], 500);
        }
    }

    private function clearClientJobCache()
    {
        // Clear multiple pages of cache (assuming up to 100 pages)
        for ($page = 1; $page <= 100; $page++) {
            Cache::forget("client_jobs_page_{$page}");
        }
    }
}
