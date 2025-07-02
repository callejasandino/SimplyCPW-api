<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGalleryRequest;
use App\Http\Requests\UUIDPageRequest;
use App\Repositories\GalleriesRepository;

class GalleriesController extends Controller
{
    private GalleriesRepository $galleriesRepository;

    public function __construct(GalleriesRepository $galleriesRepository)
    {
        $this->galleriesRepository = $galleriesRepository;
    }

    public function index(UUIDPageRequest $request)
    {
        return $this->galleriesRepository->index($request);
    }

    public function store(StoreGalleryRequest $request)
    {
        return $this->galleriesRepository->store($request);
    }

    public function destroy($id)
    {
        $shop_uuid = request()->input('shop_uuid');
        return $this->galleriesRepository->destroy($shop_uuid, $id);
    }
}
