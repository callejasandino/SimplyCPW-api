<?php

namespace App\Http\Controllers;

use App\Repositories\BusinessHoursRepository;
use Illuminate\Http\Request;

class BusinessHoursController extends Controller
{
    private BusinessHoursRepository $businessHoursRepository;

    public function __construct(BusinessHoursRepository $businessHoursRepository)
    {
        $this->businessHoursRepository = $businessHoursRepository;
    }

    public function show($shop_uuid)
    {
        return $this->businessHoursRepository->show($shop_uuid);
    }

    public function store(Request $request)
    {
        return $this->businessHoursRepository->store($request);
    }
}
