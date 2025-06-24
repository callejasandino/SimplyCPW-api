<?php

namespace App\Http\Controllers;

use App\Helpers\EncryptEmail;
use App\Mail\NewQuoteMail;
use App\Mail\UserQuoteMail;
use App\Models\Quote;
use App\Models\Service;
use App\Models\Setting;
use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $cacheKey = "pending_quotes_page_{$page}";

        $quotes = Cache::remember($cacheKey, 300, function () {
            return Quote::where('status', 'pending')->paginate(10);
        });

        return response()->json($quotes);
    }

    public function store(StoreQuoteRequest $request)
    {
        $response = Http::asForm()->post(config('captcha.url'), [
            'secret' => config('captcha.secretKey'),
            'response' => $request->input('gRecaptchaResponse'),
            'remoteip' => $request->ip(),
        ]);

        $body = $response->json();

        if (!($body['success'] ?? false)) {
            return response()->json(['message' => 'Captcha validation failed.'], 422);
        }

        $servicesNeeded = $request->input('servicesNeeded');

        $servicesNames = [];

        $isSubscribed = false;

        foreach ($servicesNeeded as $service) {
            $service = Service::where('id', $service)->first();
            $servicesNames[] = $service->name;
        }

        $quote = Quote::create([
            'firstName' => $request->input('firstName'),
            'lastName' => $request->input('lastName'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'servicesNeeded' => json_encode($servicesNeeded),
            'additionalInfo' => $request->input('additionalInfo'),
            'status' => 'pending',
            'agreedToTerms' => $request->input('agreedToTerms'),
        ]);

        if ($request->input('optIn')) {
            $this->subscribe($request->input('email'));
        }

        $setting = Setting::first();

        $quoteMail = [
            'firstName' => $quote->firstName,
            'lastName' => $quote->lastName,
            'email' => $quote->email,
            'phone' => $quote->phone,
            'address' => $quote->address,
            'servicesNeeded' => $servicesNames,
            'setting' => $setting,
        ];

        
        Mail::to($quote->email)->send(new UserQuoteMail($quoteMail, $setting->company_email));

        Mail::to($setting->company_email)->send(new NewQuoteMail($quoteMail, $setting->company_email));

        // Clear cache since new quote was created
        $this->clearQuoteCache();

        return response()->json([
            'status' => 'success',
            'message' => 'Job Quote created successfully',
            'isSubscribed' => $isSubscribed,
        ], 201);
    }

    public function update(UpdateQuoteRequest $request)
    {
        $servicesNeeded = $request->input('servicesNeeded');

        $services = [];

        foreach ($servicesNeeded as $service) {
            $service = Service::where('name', $service)->first();
            $services[] = $service->id;
        }

        $quote = Quote::findOrFail($request->id);
        $quote->update([
            'firstName' => $request->input('firstName'),
            'lastName' => $request->input('lastName'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'servicesNeeded' => json_encode($services),
            'additionalInfo' => $request->input('additionalInfo'),
            'status' => $request->input('status'),
            'agreedToTerms' => $request->input('agreedToTerms'),
        ]);

        // Clear cache since quote was updated
        $this->clearQuoteCache();

        return response()->json([
            'status' => 'success',
            'message' => 'Job Quote updated successfully',
            'quote' => $quote,
        ]);
    }

    public function destroy($id)
    {
        $quote = Quote::findOrFail($id);
        $quote->delete();

        // Clear cache since quote was deleted
        $this->clearQuoteCache();

        return response()->json([
            'status' => 'success',
            'message' => 'Quote deleted successfully',
        ]);
    }

    /**
     * Clear all cached quote pages
     */
    private function clearQuoteCache()
    {
        // Clear multiple pages of cache (assuming up to 100 pages)
        for ($page = 1; $page <= 100; $page++) {
            Cache::forget("pending_quotes_page_{$page}");
        }
    }

    private function subscribe($email)
    {
        $emailHash = (new EncryptEmail())->checkIfSubscriberExists($email);

        if (isset($emailHash['email_hash']) && isset($emailHash['encrypted_email'])) {
            Subscriber::create([
                'email' => $emailHash['encrypted_email'],
                'email_hash' => $emailHash['email_hash'],
                'options' => ['Promotional', 'Launching', 'Announcement'],
                'opt_in' => true
            ]);

            return true;
        } else if (!empty($emailHash['saved_email_hash'])) {
            return false;
        }
    }
}
