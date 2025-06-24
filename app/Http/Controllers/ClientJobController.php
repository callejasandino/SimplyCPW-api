<?php

namespace App\Http\Controllers;

use App\Helpers\ClearCache;
use App\Http\Requests\StoreClientJobRequest;
use App\Http\Requests\UpdateClientJobRequest;
use App\Mail\JobCreatedMail;
use App\Models\ClientJob;
use App\Models\Equipment;
use App\Models\Member;
use App\Models\Service;
use App\Models\Setting;
use Carbon\Carbon;
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

    public function store(StoreClientJobRequest $request)
    {
        $settingEmail = Setting::select('company_email')->first();

        try {
            $clientJob = ClientJob::create([
                'slug' => Str::uuid(),
                'title' => $request->title,
                'client' => $request->client,
                'date' => $request->date,
                'duration' => $request->duration,
                'status' => $request->status,
                'price' => $request->price,
                'notes' => $request->notes,
                'services' => $request->services,
                'team' => $request->team,
                'equipment' => $request->equipment,
            ]);

            $client = $clientJob->client;

            $jobMail = [
                'name' => $client['firstName'].' '.$client['lastName'],
                'email' => $client['email'] ?? null,
                'address' => $client['address'],
                'date' => Carbon::parse($request->date)->format('d-m-Y h:i A'),
                'duration' => $request->duration,
                'information_link' => config('vite.appUrl').'/job/'.$clientJob->slug,
                'from_email' => $settingEmail->company_email,
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

    public function update(UpdateClientJobRequest $request)
    {
        try {
            $clientJob = ClientJob::where('slug', $request->input('uuid'))->firstOrFail();
            $clientJob->update([
                'title' => $request->input('title'),
                'client' => $request->input('client'),
                'date' => $request->input('date'),
                'duration' => $request->input('duration'),
                'status' => $request->input('status'),
                'price' => $request->input('price'),
                'notes' => $request->input('notes'),
                'services' => $request->input('services'),
                'team' => $request->input('team'),
                'equipment' => $request->input('equipment'),
            ]);

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
        (new ClearCache)->clear('client_jobs_page_');
    }
}
