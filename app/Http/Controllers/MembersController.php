<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Http\Requests\UUIDPageRequest;
use App\Repositories\MembersRepository;
use Illuminate\Http\JsonResponse;

class MembersController extends Controller
{
    private MembersRepository $membersRepository;

    public function __construct(MembersRepository $membersRepository)
    {
        $this->membersRepository = $membersRepository;
    }

    public function index(UUIDPageRequest $request): JsonResponse
    {
        return $this->membersRepository->index($request);
    }

    public function store(StoreMemberRequest $request)
    {
        return $this->membersRepository->store($request);
    }

    public function update(UpdateMemberRequest $request)
    {
        return $this->membersRepository->update($request);
    }

    public function destroy(string $shop_uuid, int $id)
    {
        return $this->membersRepository->destroy($shop_uuid, $id);
    }
}
