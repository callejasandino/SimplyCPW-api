<?php

namespace App\Http\Controllers;

use App\Mail\NewQuoteMail;
use App\Mail\QuoteToUserEmail;
use App\Models\Quote;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'servicesNeeded' => 'required|array',
            'additionalInfo' => 'nullable|string|max:255',
            'agreedToTerms' => 'required|boolean',
        ]);

        $servicesNeeded = $validated['servicesNeeded'];

        $servicesNames = [];

        foreach ($servicesNeeded as $service) {
            $service = Service::where('id', $service)->first();
            $servicesNames[] = $service->name;
        }

        $quote = Quote::create([
            'firstName' => $validated['firstName'],
            'lastName' => $validated['lastName'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'servicesNeeded' => json_encode($servicesNeeded),
            'additionalInfo' => $validated['additionalInfo'],
            'status' => 'pending',
            'agreedToTerms' => $validated['agreedToTerms'],
        ]);

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

        Mail::to($quote->email)->send(new QuoteToUserEmail($quoteMail));

        Mail::to(env('INFO_EMAIL'))->send(new NewQuoteMail($quoteMail));

        // Clear cache since new quote was created
        $this->clearQuoteCache();

        return response()->json([
            'status' => 'success',
            'message' => 'Job Quote created successfully',
        ], 201);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'firstName' => 'string|max:255',
            'lastName' => 'string|max:255',
            'email' => 'email',
            'phone' => 'string|max:255',
            'address' => 'string|max:255',
            'servicesNeeded' => 'array|max:255',
            'additionalInfo' => 'string|max:255',
            'status' => 'string|max:255',
            'agreedToTerms' => 'boolean',
        ]);

        $servicesNeeded = $validated['servicesNeeded'];

        $services = [];

        foreach ($servicesNeeded as $service) {
            $service = Service::where('name', $service)->first();
            $services[] = $service->id;
        }

        $quote = Quote::findOrFail($request->id);
        $quote->update([
            'firstName' => $validated['firstName'],
            'lastName' => $validated['lastName'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'servicesNeeded' => json_encode($services),
            'additionalInfo' => $validated['additionalInfo'],
            'status' => $validated['status'],
            'agreedToTerms' => $validated['agreedToTerms'],
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
}
