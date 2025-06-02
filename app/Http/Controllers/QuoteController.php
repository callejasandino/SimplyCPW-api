<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quote;
use App\Models\Service;

class QuoteController extends Controller
{
    public function index() {
        $perPage = request()->query('per_page', 10);
        $quotes = Quote::paginate($perPage);
        return response()->json($quotes);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'servicesNeeded' => 'required|string|max:255',
            'additionalInfo' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'agreedToTerms' => 'required|boolean',
        ]);

        $servicesNeeded = json_decode($validated['servicesNeeded'], true);
        
        $services = [];

        foreach ($servicesNeeded as $service) {
            $service = Service::where('name', $service)->first();
            $services[] = $service->id;
        }
        
        Quote::create([
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

        return response()->json([
            'status' => 'success',
            'message' => 'Job Quote created successfully'
        ], 201);
    }

    public function update(Request $request) {
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

        return response()->json([
            'status' => 'success',
            'message' => 'Job Quote updated successfully',
            'quote' => $quote
        ]);
    }

    public function destroy($id) {
        $quote = Quote::findOrFail($id);
        $quote->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Quote deleted successfully'
        ]);
    }
}
