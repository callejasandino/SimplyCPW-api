<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Interfaces\ServicesInterface;
use Illuminate\Http\JsonResponse;

class ServicesController extends Controller
{
    public function __construct(private ServicesInterface $servicesInterface) {}

    public function index(string $shop_uuid): JsonResponse
    {
        return $this->servicesInterface->index($shop_uuid);
    }

    public function show(string $shop_uuid, string $slug): JsonResponse
    {
        return $this->servicesInterface->show($shop_uuid, $slug);
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        return $this->servicesInterface->store($request);
    }

    public function update(UpdateServiceRequest $request): JsonResponse
    {
        return $this->servicesInterface->update($request);
    }

    public function destroy(string $shop_uuid, string $slug): JsonResponse
    {
        return $this->servicesInterface->destroy($shop_uuid, $slug);
    }
}
