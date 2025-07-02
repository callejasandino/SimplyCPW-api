<?php

namespace App\Http\Controllers;

use App\Repositories\SubscribersRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    private SubscribersRepository $subscribersRepository;

    public function __construct(SubscribersRepository $subscribersRepository)
    {
        $this->subscribersRepository = $subscribersRepository;
    }

    public function subscribe(Request $request): JsonResponse
    {
        return $this->subscribersRepository->subscribe($request);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        return $this->subscribersRepository->unsubscribe($request);
    }
}
