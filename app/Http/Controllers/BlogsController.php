<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Http\Requests\UUIDPageRequest;
use App\Repositories\BlogsRepository;
use Illuminate\Http\JsonResponse;

class BlogsController extends Controller
{
    private BlogsRepository $blogRepository;

    public function __construct(BlogsRepository $blogRepository)
    {
        $this->blogRepository = $blogRepository;
    }

    public function index(UUIDPageRequest $request): JsonResponse
    {
        return $this->blogRepository->index($request);
    }

    public function store(StoreBlogRequest $request): JsonResponse
    {
        return $this->blogRepository->store($request);
    }

    public function show($shop_uuid, $slug): JsonResponse
    {
        return $this->blogRepository->show($shop_uuid, $slug);
    }

    public function update(UpdateBlogRequest $request): JsonResponse
    {
        return $this->blogRepository->update($request);
    }

    public function destroy($shop_uuid, $slug): JsonResponse
    {
        return $this->blogRepository->destroy($shop_uuid, $slug);
    }
}
