<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Helpers\UploadImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        $setting = Setting::first();

        return response()->json([
            'settings' => $setting,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string',
            'company_address' => 'required|string',
            'company_phone' => 'required|string',
            'company_email' => 'required|string',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'company_description' => 'required|string',
            'company_facebook' => 'nullable|string',
            'company_instagram' => 'nullable|string',
            'company_twitter' => 'nullable|string',
            'company_linkedin' => 'nullable|string',
            'company_youtube' => 'nullable|string',
            'company_tiktok' => 'nullable|string',
            'company_pinterest' => 'nullable|string',
            'company_story' => 'required|string',
            'company_mission' => 'required|string',
            'company_vision' => 'required|string',
            'areas_served' => 'array',
            'faqs' => 'array',
        ]);

        $image = null;

        // Handle main image upload
        if ($request->hasFile('company_logo')) {
            $image = (new UploadImage)->uploadImage('settings', $request->file('company_logo'));
        }

        $setting = Setting::first();

        if ($setting) {
            $setting->update([
                'company_name' => $validated['company_name'],
                'company_address' => $validated['company_address'],
                'company_phone' => $validated['company_phone'],
                'company_email' => $validated['company_email'],
                'company_logo_filename' => $image['filename'],
                'company_logo' => $image['path'],
                'company_description' => $validated['company_description'],
                'company_facebook' => $validated['company_facebook'],
                'company_instagram' => $validated['company_instagram'],
                'company_twitter' => $validated['company_twitter'],
                'company_linkedin' => $validated['company_linkedin'],
                'company_youtube' => $validated['company_youtube'],
                'company_tiktok' => $validated['company_tiktok'],
                'company_pinterest' => $validated['company_pinterest'],
                'company_story' => $validated['company_story'],
                'company_mission' => $validated['company_mission'],
                'company_vision' => $validated['company_vision'],
                'areas_served' => json_encode($validated['areas_served']),
                'faqs' => json_encode($validated['faqs']),
            ]);
        } else {
            $setting = Setting::create([
                'company_name' => $validated['company_name'],
                'company_address' => $validated['company_address'],
                'company_phone' => $validated['company_phone'],
                'company_email' => $validated['company_email'],
                'company_logo_filename' => $image['filename'],
                'company_logo' => $image['path'],
                'company_description' => $validated['company_description'],
                'company_facebook' => $validated['company_facebook'],
                'company_instagram' => $validated['company_instagram'],
                'company_twitter' => $validated['company_twitter'],
                'company_linkedin' => $validated['company_linkedin'],
                'company_youtube' => $validated['company_youtube'],
                'company_tiktok' => $validated['company_tiktok'],
                'company_pinterest' => $validated['company_pinterest'],
                'company_story' => $validated['company_story'],
                'company_mission' => $validated['company_mission'],
                'company_vision' => $validated['company_vision'],
                'areas_served' => json_encode($validated['areas_served']),
                'faqs' => json_encode($validated['faqs']),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Setting updated successfully',
            'data' => $setting,
        ]);
    }
}
