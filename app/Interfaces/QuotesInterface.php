<?php

namespace App\Interfaces;

use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Http\Requests\UUIDPageRequest;
use App\Models\Shop;

interface QuotesInterface
{
    public function index(UUIDPageRequest $request);

    public function store(StoreQuoteRequest $request);

    public function update(UpdateQuoteRequest $request);

    public function destroy(string $shop_uuid, int $id);

    public function clearQuoteCache(Shop $shop);

    public function subscribe(string $email, string $shop_uuid);
}
