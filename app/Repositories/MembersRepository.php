<?php

namespace App\Repositories;

use App\Helpers\ApiResponse;
use App\Helpers\ClearCache;
use App\Helpers\UploadImage;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Interfaces\MembersInterface;
use App\Models\Member;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class MembersRepository implements MembersInterface
{
    public function index(string $shop_uuid): JsonResponse
    {
        $shop = $this->getShopByUuid($shop_uuid);
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $page = request()->get('page', 1);
        $cacheKey = "members_page_{$page}_shop_{$shop->id}";

        $members = Cache::remember($cacheKey, 300, fn () => Member::where('shop_id', $shop->id)->paginate(10));

        return ApiResponse::success(['members' => $members]);
    }

    public function store(StoreMemberRequest $request): JsonResponse
    {
        $shop = $this->getShopByUuid($request->input('shop_uuid'));

        if (! $shop) {
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
        $shop = $this->getShopByUuid($request->input('shop_uuid'));

        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $member = $this->getMemberById($shop, $request->input('id'));

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
        $shop = $this->getShopByUuid($shop_uuid);
        if (! $shop) {
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

    public function getShopByUuid(string $shop_uuid): ?Shop
    {
        return Shop::where('uuid', $shop_uuid)->first();
    }

    public function getMemberById(Shop $shop, int $id): Member
    {
        return $shop->members()->findOrFail($id);
    }
}
