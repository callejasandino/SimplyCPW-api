<?php

namespace App\Http\Controllers;

use App\Helpers\ClearCache;
use App\Helpers\DeleteImage;
use App\Helpers\UploadImage;
use App\Http\Requests\StoreBusinessEventRequest;
use App\Http\Requests\UpdateBusinessEventRequest;
use App\Jobs\SendNewsletterEmail;
use App\Mail\JobCreatedMail;
use App\Models\BusinessEvent;
use App\Models\ClientJob;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Shop;
use App\Models\Subscriber;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class BusinessEventController extends Controller
{
    public function clientIndex($shop_uuid)
    {
        $shop = Shop::where('uuid', $shop_uuid)->first();

        if (! $shop) {
            return response()->json([
                'message' => 'Shop not found',
            ], 404);
        }

        $page = request()->get('page', 1);
        $cacheKey = "client_business_events_page_{$page}";

        $businessEvents = Cache::remember($cacheKey, 300, function () {
            return BusinessEvent::where('status', 'published')
                ->where('visible', 1)
                ->paginate(10);
        });

        return response()->json(
            [
                'status' => 'success',
                'business_events' => $businessEvents,
            ],
            200
        );
    }

    public function index()
    {
        $page = request()->get('page', 1);
        $cacheKey = "business_events_page_{$page}";

        $businessEvents = Cache::remember($cacheKey, 300, function () {
            return BusinessEvent::paginate(10);
        });

        return response()->json(
            [
                'status' => 'success',
                'business_events' => $businessEvents,
            ],
            200
        );
    }

    public function store(StoreBusinessEventRequest $request)
    {
        $eventType = $request->input('event_type');
        $imageUrl = null;
        $businessEvent = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageUrl = (new UploadImage)->uploadImage('business-events', $image);
        }

        if ($eventType == 'launching' || $eventType == 'announcement') {
            $businessEvent = BusinessEvent::create([
                'title' => $request->input('title'),
                'slug' => Str::uuid(),
                'description' => $request->input('description'),
                'event_type' => $request->input('event_type'),
                'filename' => $imageUrl['filename'],
                'image' => $imageUrl['path'],
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'status' => $request->input('status'),
                'cta_link' => env('VITE_APP_URL').'/business-events/'.$request->input('slug'),
                'cta_label' => $request->input('cta_label'),
                'visible' => $request->input('visible'),
            ]);
        }

        if ($eventType == 'promotional') {
            $businessEvent = BusinessEvent::create([
                'title' => $request->input('title'),
                'slug' => Str::uuid(),
                'description' => $request->input('description'),
                'event_type' => $request->input('event_type'),
                'filename' => $imageUrl['filename'],
                'image' => $imageUrl['path'],
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'status' => $request->input('status'),
                'cta_link' => config('vite.appUrl').'/business-events/'.$request->input('slug'),
                'cta_label' => $request->input('cta_label'),
                'visible' => $request->input('visible'),
                'discounted_services' => json_decode($request->input('discounted_services')),
            ]);
        }

        if ($businessEvent->status == 'published' && $businessEvent->visible == 1) {
            $this->sendNewsletter($businessEvent, $eventType);
        }

        $this->clearBusinessEventCache();

        return response()->json(
            [
                'status' => 'success',
                'message' => 'Business event created successfully',
                'business_event' => $businessEvent,
            ], 200);
    }

    public function show($slug)
    {
        $businessEvent = BusinessEvent::where('slug', $slug)->first();

        return response()->json(
            [
                'status' => 'success',
                'business_event' => $businessEvent,
            ], 200);
    }

    public function showDiscountedServices($slug)
    {
        $businessEvent = BusinessEvent::where('slug', $slug)->where('status', 'published')->first();

        if (! $businessEvent) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Event no longer available',
                ], 404);
        }

        $services = Service::all();

        $finalDiscountedServices = [];

        $discountedServices = $businessEvent->discounted_services;

        $discountMap = array_column($discountedServices, 'discount', 'service');

        foreach ($services as $service) {
            $discount = $discountMap[$service->name] ?? 0;

            if ($discount) {
                $finalDiscountedServices['discounted_services'][] = [
                    'service' => $service->name,
                    'discount' => $discount,
                    'original_price' => $service->price,
                    'discounted_price' => $service->price * (1 - $discount / 100),
                    'duration' => $service->duration,
                ];
            } else {
                $finalDiscountedServices['non_discounted_services'][] = [
                    'service' => $service->name,
                    'discount' => 0,
                    'original_price' => $service->price,
                    'discounted_price' => $service->price,
                    'duration' => $service->duration,
                ];
            }
        }

        return response()->json(
            [
                'status' => 'success',
                'discounted_services' => $finalDiscountedServices,
            ], 200);
    }

    public function update(UpdateBusinessEventRequest $request)
    {
        $businessEvent = BusinessEvent::where('slug', $request->input('slug'))->first();

        if (! $businessEvent) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Business event not found',
                ], 404);
        }

        $eventType = $request->input('event_type');

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageUrl = (new UploadImage)->uploadImage('business-events', $image);
            (new DeleteImage)->deleteImage('business-events/'.$businessEvent->filename);
        } else {
            $imageUrl = [
                'filename' => $businessEvent->filename,
                'path' => $businessEvent->image,
            ];
        }

        if ($eventType == 'launching' || $eventType == 'announcement') {
            $businessEvent->update([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'event_type' => $request->input('event_type'),
                'filename' => $imageUrl['filename'],
                'image' => $imageUrl['path'],
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'status' => $request->input('status'),
                'cta_link' => env('VITE_APP_URL').'/business-events/'.$request->input('slug'),
                'cta_label' => $request->input('cta_label'),
                'visible' => $request->input('visible'),
            ]);
        }

        if ($eventType == 'promotional') {
            $businessEvent->update([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'event_type' => $request->input('event_type'),
                'filename' => $imageUrl['filename'],
                'image' => $imageUrl['path'],
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'status' => $request->input('status'),
                'cta_link' => env('VITE_APP_URL').'/business-events/'.$request->input('slug'),
                'cta_label' => $request->input('cta_label'),
                'visible' => $request->input('visible'),
                'discounted_services' => json_decode($request->input('discounted_services')),
            ]);
        }

        $this->clearBusinessEventCache();

        return response()->json(
            [
                'status' => 'success',
                'message' => 'Business event updated successfully',
                'business_event' => $businessEvent,
            ], 200);
    }

    public function delete($slug)
    {
        $businessEvent = BusinessEvent::where('slug', $slug)->first();
        (new DeleteImage)->deleteImage('business-events/'.$businessEvent->filename);
        $businessEvent->delete();
        $this->clearBusinessEventCache();

        return response()->json(['status' => 'success', 'message' => 'Business event deleted successfully'], 200);
    }

    public function processClientBooking(Request $request)
    {
        $response = Http::asForm()->post(config('captcha.url'), [
            'secret' => config('captcha.secretKey'),
            'response' => $request->input('gRecaptchaResponse'),
            'remoteip' => $request->ip(),
        ]);

        $body = $response->json();

        if (! ($body['success'] ?? false)) {
            return response()->json(['message' => 'Captcha validation failed.'], 422);
        }

        $settingEmail = Setting::select('company_email')->first();

        $title = 'Job for '.$request->input('last_name').' '.$request->input('first_name');

        $servicesIds = [];

        $client = [
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'lastName' => $request->input('last_name'),
            'firstName' => $request->input('first_name'),
        ];

        $selectedServices = $request->input('selectedServices');

        foreach ($selectedServices as $selectedService) {
            $service = Service::where('name', $selectedService['service_name'])->first();

            $servicesIds[] = $service->id;
        }

        $clientJob = ClientJob::create([
            'slug' => Str::uuid(),
            'title' => $title,
            'client' => $client,
            'date' => Carbon::parse($request->input('dateTime'))->format('Y-m-d H:i:s'),
            'services' => $servicesIds,
            'duration' => $request->input('duration'),
            'status' => $request->input('status'),
            'price' => $request->input('price'),
        ]);

        $client = $clientJob->client;

        $jobMail = [
            'name' => $client['firstName'].' '.$client['lastName'],
            'email' => $client['email'] ?? null,
            'address' => $client['address'],
            'date' => Carbon::parse($request->input('dateTime'))->format('Y-m-d H:i:s'),
            'duration' => $request->input('duration'),
            'information_link' => config('vite.appUrl').'/job/'.$clientJob->slug,
            'from_email' => $settingEmail->company_email,
        ];

        Mail::to($jobMail['email'])->send(new JobCreatedMail($jobMail));

        return response()->json([
            'status' => 'success',
            'message' => 'Client job created successfully',
            'client_job' => $clientJob,
        ], 200);
    }

    private function clearBusinessEventCache()
    {
        (new ClearCache)->clear('business_events_page_');
    }

    private function sendNewsletter($businessEvent, $eventType)
    {
        $subcribers = Subscriber::where('opt_in', true)->whereJsonContains('options', $eventType)->get();

        Bus::batch(
            $subcribers->map(fn ($subcriber) => new SendNewsletterEmail($subcriber->email, $businessEvent))
        )->then(function (Batch $batch) {
            // All jobs completed successfully
            Log::info('Newsletter batch completed!', ['batch_id' => $batch->id]);
        })
            ->catch(function (Throwable $e) {
                // One or more jobs failed
                Log::error('Newsletter batch failed.', ['error' => $e->getMessage()]);
            })
            ->finally(function () {
                // Always executed at the end
                Log::info('Newsletter batch has finished processing.');
            })
            ->dispatch();
    }
}
