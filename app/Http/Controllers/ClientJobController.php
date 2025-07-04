<?php

namespace App\Http\Controllers;

use App\Http\Requests\UUIDPageRequest;
use App\Http\Requests\StoreClientJobRequest;
use App\Http\Requests\UpdateClientJobRequest;
use App\Repositories\ClientJobRepository;
use Illuminate\Http\JsonResponse;

class ClientJobController extends Controller
{
    protected $clientJobRepository;

    public function __construct(ClientJobRepository $clientJobRepository)
    {
        $this->clientJobRepository = $clientJobRepository;
    }

    public function index(UUIDPageRequest $request): JsonResponse
    {
        return $this->clientJobRepository->index($request);
    }

    public function show($slug): JsonResponse
    {
        return $this->clientJobRepository->show($slug);
    }

    public function store(StoreClientJobRequest $request): JsonResponse
    {
        return $this->clientJobRepository->store($request);
    }

    public function update(UpdateClientJobRequest $request): JsonResponse
    {
        return $this->clientJobRepository->update($request);
    }

    public function destroy(UUIDPageRequest $request, $slug): JsonResponse
    {
        return $this->clientJobRepository->destroy($request, $slug);
    }
}
