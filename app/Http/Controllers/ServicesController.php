<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Requests\UUIDPageRequest;
use App\Interfaces\ServicesInterface;
use App\Repositories\ServicesRepository;
use Illuminate\Http\JsonResponse;

class ServicesController extends Controller
{
    private ServicesRepository $servicesRepository;

    public function __construct(ServicesRepository $servicesRepository)
    {
        $this->servicesRepository = $servicesRepository;
    }

    public function index(UUIDPageRequest $request): JsonResponse
    {
        return $this->servicesRepository->index($request);
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        return $this->servicesRepository->store($request);
    }

    public function update(UpdateServiceRequest $request): JsonResponse
    {
        return $this->servicesRepository->update($request);
    }

    public function destroy(string $shop_uuid, string $id): JsonResponse
    {
        return $this->servicesRepository->destroy($shop_uuid, $id);
    }
}
