<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Http\Requests\UUIDPageRequest;
use App\Repositories\QuotesRepository;
use Illuminate\Http\JsonResponse;

class QuotesController extends Controller
{
    private QuotesRepository $quotesRepository;

    public function __construct(QuotesRepository $quotesRepository)
    {
        $this->quotesRepository = $quotesRepository;
    }

    public function index(UUIDPageRequest $request): JsonResponse
    {
        return $this->quotesRepository->index($request);
    }

    public function store(StoreQuoteRequest $request): JsonResponse
    {
        return $this->quotesRepository->store($request);
    }

    public function update(UpdateQuoteRequest $request): JsonResponse
    {
        return $this->quotesRepository->update($request);
    }

    public function destroy(string $shop_uuid, int $id): JsonResponse
    {
        return $this->quotesRepository->destroy($shop_uuid, $id);
    }
}
