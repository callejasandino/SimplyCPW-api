<?php

namespace App\Repositories;

use App\Helpers\ApiResponse;
use App\Interfaces\BusinessHoursInterface;
use App\Models\BusinessHour;
use App\Models\Shop;
use Illuminate\Http\Request;

class BusinessHoursRepository implements BusinessHoursInterface
{
    public function show($shop_uuid)
    {
        $shop = $this->getShopByUuid($shop_uuid);

        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $businessHours = BusinessHour::where('shop_id', $shop->id)->first();

        if (! $businessHours) {
            return ApiResponse::error('Business hours not found', 404);
        }

        return ApiResponse::success([
            'businessHours' => $businessHours,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'hours' => 'required|array',
            'shop_uuid' => 'required|string|exists:shops,uuid',
        ]);

        $shop = $this->getShopByUuid($request->input('shop_uuid'));

        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $businessHours = $shop->businessHours;

        if ($businessHours) {
            $businessHours->update([
                'hours' => json_encode($request->input('hours')),
            ]);
        } else {
            BusinessHour::create([
                'hours' => json_encode($request->input('hours')),
                'shop_id' => $shop->id,
            ]);
        }

        return ApiResponse::success([
            'message' => 'Business hours created successfully',
        ], 201);
    }

    private function getShopByUuid(string $uuid): ?Shop
    {
        return Shop::where('uuid', $uuid)->first();
    }
}
