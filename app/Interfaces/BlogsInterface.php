<?php

namespace App\Interfaces;

use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Http\Requests\UUIDPageRequest;
use App\Models\Shop;

interface BlogsInterface
{
    public function index(UUIDPageRequest $request);

    public function store(StoreBlogRequest $request);

    public function show($shop_uuid, $slug);

    public function update(UpdateBlogRequest $request);

    public function destroy($shop_uuid, $slug);

    public function clearBlogCache(Shop $shop);
}
