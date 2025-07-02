<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShopRequest;
use App\Http\Requests\UpdateShopRequest;
use App\Repositories\ShopsRepository;
use Illuminate\Http\JsonResponse;

class ShopsController extends Controller
{
    public function __construct(private ShopsRepository $shopsRepository) {}

    public function index(): JsonResponse
    {
        return $this->shopsRepository->index();
    }

    public function show(string $uuid): JsonResponse
    {
        return $this->shopsRepository->show($uuid);
    }

    public function store(StoreShopRequest $request): JsonResponse
    {
        return $this->shopsRepository->store($request);
    }

    public function update(UpdateShopRequest $request): JsonResponse
    {
        return $this->shopsRepository->update($request);
    }

    public function destroy(string $uuid): JsonResponse
    {
        return $this->shopsRepository->destroy($uuid);
    }
}
