<?php

namespace App\Interfaces;

use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Shop;

interface MembersInterface
{
    public function index(string $shop_uuid);

    public function store(StoreMemberRequest $request);

    public function update(UpdateMemberRequest $request);

    public function destroy(string $shop_uuid, int $id);

    public function clearMemberCache(Shop $shop);

    public function getShopByUuid(string $shop_uuid);

    public function getMemberById(Shop $shop, int $id);
}
