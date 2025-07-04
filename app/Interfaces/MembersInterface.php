<?php

namespace App\Interfaces;

use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Http\Requests\UUIDPageRequest;
use App\Models\Member;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;

interface MembersInterface
{
    public function index(UUIDPageRequest $request) : JsonResponse;

    public function store(StoreMemberRequest $request) : JsonResponse;

    public function update(UpdateMemberRequest $request) : JsonResponse;

    public function destroy(string $shop_uuid, int $id) : JsonResponse;

    public function clearMemberCache(Shop $shop) : void;

    public function getShopByUuid(string $shop_uuid) : Shop;

    public function getMemberById(int $id) : Member;
}
