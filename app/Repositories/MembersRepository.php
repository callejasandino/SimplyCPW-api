<?php

namespace App\Repositories;

use App\Helpers\ApiResponse;
use App\Helpers\ClearCache;
use App\Helpers\UploadImage;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Http\Requests\UUIDPageRequest;
use App\Interfaces\MembersInterface;
use App\Models\Member;
use App\Models\Shop;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class MembersRepository implements MembersInterface
{
    public function index(UUIDPageRequest $request): JsonResponse
    {
        try {
            $shop = $this->getShopByUuid($request->input('shop_uuid'));
        } catch (Exception $e) {
            return ApiResponse::error('Shop not found', 404);
        }

        $page = $request->input('page', 1);
        $search = $request->input('search');
        $cacheKey = "members_page_{$page}_shop_{$shop->id}";

        $members = Cache::remember($cacheKey, 300, function () use ($shop, $search) {
            $query = Member::where('shop_id', $shop->id);

            if ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%')
                ->orWhere('contact_number', 'like', '%'.$search.'%');
            }

            return $query->paginate(10);
        });

        return ApiResponse::success($members, 'Members fetched successfully');
    }

    public function store(StoreMemberRequest $request): JsonResponse
    {
        try {
            $shop = $this->getShopByUuid($request->input('shop_uuid'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('Shop not found', 404);
        }

        $imageUrl = $request->hasFile('image')
            ? (new UploadImage)->uploadImage('members', $request->file('image'))
            : ['filename' => null, 'path' => null];

        $member = Member::create([
            'shop_id' => $shop->id,
            'name' => $request->input('name'),
            'filename' => $imageUrl['filename'],
            'path' => $imageUrl['path'],
            'email' => $request->input('email'),
            'contact_number' => $request->input('contact_number'),
            'shop_id' => $shop->id,
        ]);

        $this->clearMemberCache($shop);

        return ApiResponse::success(['member' => $member], 'Member created successfully', 201);
    }

    public function update(UpdateMemberRequest $request): JsonResponse
    {
        try {
            $shop = $this->getShopByUuid($request->input('shop_uuid'));
        } catch (Exception $e) {
            return ApiResponse::error('Shop not found', 404);
        }

        $member = $this->getMemberById($request->input('id'));

        $imageUrl = $request->hasFile('image')
            ? (new UploadImage)->uploadImage('members', $request->file('image'))
            : ['filename' => $member->filename, 'path' => $member->path];

        $member->update([
            'name' => $request->input('name'),
            'filename' => $imageUrl['filename'],
            'path' => $imageUrl['path'],
            'email' => $request->input('email'),
            'contact_number' => $request->input('contact_number'),
        ]);

        $this->clearMemberCache($shop);

        return ApiResponse::success(['member' => $member], 'Member updated successfully');
    }

    public function destroy(string $shop_uuid, int $id): JsonResponse
    {
        try {
            $shop = $this->getShopByUuid($shop_uuid);
        } catch (Exception $e) {
            return ApiResponse::error('Shop not found', 404);
        }

        $member = Member::where('shop_id', $shop->id)->findOrFail($id);
        $member->delete();

        $this->clearMemberCache($shop);

        return ApiResponse::success([], 'Member deleted successfully');
    }

    public function clearMemberCache(Shop $shop): void
    {
        (new ClearCache)->clear('members_page_', $shop->id);
    }

    public function getShopByUuid(string $shop_uuid) : Shop
    {
        return Shop::where('uuid', $shop_uuid)->firstOrFail();
    }

    public function getMemberById(int $id): Member
    {
        return Member::findOrFail($id);
    }
}
