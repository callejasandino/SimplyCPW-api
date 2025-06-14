<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class MemberController extends Controller
{
    public function index()
    {
        $page = request()->get('page', 1);
        $cacheKey = "members_page_{$page}";
        $members = Cache::remember($cacheKey, 300, function () {
            return Member::all();
        });

        return response()->json([
            'members' => $members,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|sometimes',
            'email' => 'required|string|max:255',
            'contact_number' => 'required|string|max:255',
        ]);

        $imageUrl = $this->uploadImage($request->file('image'), null);

        $member = Member::create([
            'name' => $validated['name'],
            'image' => $imageUrl,
            'email' => $validated['email'],
            'contact_number' => $validated['contact_number'],
        ]);

        $this->clearMemberCache();

        return response()->json([
            'member' => $member,
            'message' => 'Member created successfully',
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'image' => 'string|max:255',
            'email' => 'string|max:255',
            'contact_number' => 'string|max:255',
        ]);

        $member = Member::findOrFail($request->id);

        $imageUrl = $this->uploadImage($request->file('image'), $member);

        $member->update([
            'name' => $validated['name'],
            'image' => $imageUrl,
            'email' => $validated['email'],
            'contact_number' => $validated['contact_number'],
        ]);

        $this->clearMemberCache();

        return response()->json([
            'member' => $member,
            'message' => 'Member updated successfully',
        ]);
    }

    public function destroy($id)
    {
        $member = Member::findOrFail($id);
        $member->delete();

        $this->clearMemberCache();

        return response()->json(['message' => 'Member deleted successfully']);
    }

    public function uploadImage($imageFile, $member)
    {
        if ($imageFile) {
            // Handle file upload
            $image = $imageFile;

            // Generate a unique filename with timestamp and original extension
            $filename = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();

            // Store the image in the gallery directory within the public disk
            $path = $image->storeAs('members', $filename, 'public');

            $imageUrl = url(Storage::url($path));
        } else {
            // Keep existing image
            $imageUrl = $member->image;
        }

        return $imageUrl;
    }

    private function clearMemberCache()
    {
        // Clear multiple pages of cache (assuming up to 100 pages)
        for ($page = 1; $page <= 100; $page++) {
            Cache::forget("members_page_{$page}");
        }
    }
}
