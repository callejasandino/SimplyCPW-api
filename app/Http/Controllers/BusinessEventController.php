<?php

namespace App\Http\Controllers;

use App\Helpers\ClearCache;
use App\Helpers\DeleteImage;
use App\Helpers\UploadImage;
use App\Http\Requests\StoreBusinessEventRequest;
use App\Http\Requests\UpdateBusinessEventRequest;
use App\Mail\NewsletterMail;
use App\Models\BusinessEvent;
use App\Models\Subscribe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class BusinessEventController extends Controller
{
    public function clientIndex()
    {
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
                'slug' => $request->input('slug'),
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
                'slug' => $request->input('slug'),
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
            $this->sendNewsletter($businessEvent);
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
                'slug' => $request->input('slug'),
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
                'slug' => $request->input('slug'),
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

    public function delete($id)
    {
        $businessEvent = BusinessEvent::where('id', $id)->first();
        (new DeleteImage)->deleteImage('business-events/'.$businessEvent->filename);
        $businessEvent->delete();
        $this->clearBusinessEventCache();

        return response()->json(['status' => 'success', 'message' => 'Business event deleted successfully'], 200);
    }

    private function clearBusinessEventCache()
    {
        (new ClearCache)->clear('business_events_page_');
    }

    private function sendNewsletter($businessEvent)
    {
        $subscribers = Subscribe::where('opt_in', true)->get();

        foreach ($subscribers as $subscriber) {
            $decryptedEmail = Crypt::decryptString($subscriber->email);
            
            Mail::to($decryptedEmail)->send(new NewsletterMail($businessEvent));
            
            sleep(2);
        }
    }
}
