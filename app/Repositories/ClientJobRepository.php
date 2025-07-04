<?php

namespace App\Repositories;

use App\Interfaces\ClientJobsInterface;
use App\Helpers\ApiResponse;
use App\Helpers\ClearCache;
use App\Http\Requests\StoreClientJobRequest;
use App\Http\Requests\UpdateClientJobRequest;
use App\Http\Requests\UUIDPageRequest;
use App\Mail\JobCreatedMail;
use App\Models\ClientJob;
use App\Models\Member;
use App\Models\Service;
use App\Models\Shop;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ClientJobRepository implements ClientJobsInterface
{
    public function index(UUIDPageRequest $request): JsonResponse
    {
        $shop = $this->getShopByUuid($request->input('shop_uuid'));
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $page = $request->input('page', 1);
        $search = $request->input('search');
        $cacheKey = "client_jobs_page_{$page}_shop_{$shop->id}";

        $clientJobs = Cache::remember($cacheKey, 300, function () use ($shop, $search) {
            $query = ClientJob::where('shop_id', $shop->id);

            if ($search) {
                $query->where('title', 'like', '%'.$search.'%');
            }

            return $query->paginate(10);
        });

        return ApiResponse::success($clientJobs, 'Client jobs fetched successfully');
    }

    public function show($slug): JsonResponse
    {
        $clientJob = ClientJob::where('slug', $slug)->firstOrFail()->makeHidden(['id', 'slug', 'created_at', 'updated_at', 'client.id', 'client.phone']);

        $services = $clientJob->services;
        $serviceNames = [];
        $memberNames = [];

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
        $clientJob->team = $memberNames;

        return ApiResponse::success($clientJob, 'Client job fetched successfully');
    }

    public function store(StoreClientJobRequest $request): JsonResponse
    {
        $shop = $this->getShopByUuid($request->input('shop_uuid'));
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

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
            ]);

            $client = $clientJob->client;

            $jobMail = [
                'name' => $client['firstName'].' '.$client['lastName'],
                'email' => $client['email'] ?? null,
                'address' => $client['address'],
                'date' => Carbon::parse($request->date)->format('d-m-Y h:i A'),
                'duration' => $request->duration,
                'information_link' => config('vite.appUrl').'/job/'.$clientJob->slug,
                'from_email' => $shop->email,
            ];

            Mail::to($jobMail['email'])->send(new JobCreatedMail($jobMail));

            $this->clearClientJobCache($shop);

            return ApiResponse::success($clientJob, 'Client job created successfully');
        } catch (Exception $e) {
            Log::error('Error creating client job: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error creating client job',
            ], 500);
        }
    }

    public function update(UpdateClientJobRequest $request): JsonResponse
    {
        $shop = $this->getShopByUuid($request->input('shop_uuid'));
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

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
            ]);

            $this->clearClientJobCache($shop);

            return ApiResponse::success($clientJob, 'Client job updated successfully');
        } catch (Exception $e) {
            Log::error('Error updating client job: '.$e->getMessage());
            return ApiResponse::error('Error updating client job', 500);
        }
    }

    public function destroy(UUIDPageRequest $request, $slug): JsonResponse
    {
        $shop = $this->getShopByUuid($request->input('shop_uuid'));
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        try {
            $clientJob = ClientJob::where('slug', $slug)->firstOrFail();
            $clientJob->delete();

            $this->clearClientJobCache($shop);

            return ApiResponse::success([], 'Client job deleted successfully');
        } catch (Exception $e) {
            Log::error('Error deleting client job: '.$e->getMessage());
            return ApiResponse::error('Error deleting client job', 500);
        }
    }

    private function clearClientJobCache(Shop $shop): void
    {
        (new ClearCache)->clear('client_jobs_page_', $shop->id);
    }

    private function getShopByUuid(string $uuid): ?Shop
    {
        return Shop::where('uuid', $uuid)->first();
    }
}
