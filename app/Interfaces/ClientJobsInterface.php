<?php

namespace App\Interfaces;

use App\Http\Requests\StoreClientJobRequest;
use App\Http\Requests\UpdateClientJobRequest;
use App\Http\Requests\UUIDPageRequest;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;

interface ClientJobsInterface
{
    public function index(UUIDPageRequest $request) : JsonResponse;
    public function show($slug) : JsonResponse;
    public function store(StoreClientJobRequest $request) : JsonResponse;
    public function update(UpdateClientJobRequest $request) : JsonResponse;
    public function destroy(UUIDPageRequest $request, $slug) : JsonResponse;
}
