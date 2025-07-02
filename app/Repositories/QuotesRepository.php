<?php

namespace App\Repositories;

use App\Helpers\ApiResponse;
use App\Helpers\ClearCache;
use App\Helpers\EncryptEmail;
use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Http\Requests\UUIDPageRequest;
use App\Interfaces\QuotesInterface;
use App\Models\Quote;
use App\Models\Service;
use App\Models\Shop;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class QuotesRepository implements QuotesInterface
{
    public function index(UUIDPageRequest $request): JsonResponse
    {
        $shop = $this->getShopByUuid($request->input('shop_uuid'));
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $page = request()->get('page', 1);
        $cacheKey = "pending_quotes_page_{$page}_shop_{$shop->id}";

        $quotes = Cache::remember($cacheKey, 300, fn () => $shop->quotes()
            ->where('status', 'pending')
            ->paginate(10)
        );

        return ApiResponse::success(['quotes' => $quotes], 'Quotes fetched successfully');
    }

    public function store(StoreQuoteRequest $request): JsonResponse
    {
        $captcha = Http::asForm()->post(config('captcha.url'), [
            'secret' => config('captcha.secretKey'),
            'response' => $request->input('gRecaptchaResponse'),
            'remoteip' => $request->ip(),
        ])->json();

        if (! ($captcha['success'] ?? false)) {
            return ApiResponse::error('Captcha validation failed.', 422);
        }

        $shop = $this->getShopByUuid($request->input('shop_uuid'));
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $serviceNames = $request->input('servicesNeeded', []);
        $serviceIds = Service::where('shop_id', $shop->id)->whereIn('name', $serviceNames)->pluck('id')->toArray();

        Quote::create([
            'shop_id' => $shop->id,
            'firstName' => $request->input('firstName'),
            'lastName' => $request->input('lastName'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'servicesNeeded' => json_encode($serviceIds),
            'additionalInfo' => $request->input('additionalInfo'),
            'status' => 'pending',
            'agreedToTerms' => $request->input('agreedToTerms'),
        ]);

        $isSubscribed = false;
        if ($request->boolean('optIn')) {
            $isSubscribed = $this->subscribe($request->input('email'), $shop->uuid);
        }

        $this->clearQuoteCache($shop);

        return ApiResponse::success(['isSubscribed' => $isSubscribed], 'Job Quote created successfully', 201);
    }

    public function update(UpdateQuoteRequest $request): JsonResponse
    {
        $shop = $this->getShopByUuid($request->input('shop_uuid'));
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $quote = $shop->quotes()->findOrFail($request->input('id'));

        $serviceNames = $request->input('servicesNeeded', []);
        $serviceIds = Service::where('shop_id', $shop->id)
            ->whereIn('name', $serviceNames)
            ->pluck('id')
            ->toArray();

        $quote->update([
            'firstName' => $request->input('firstName'),
            'lastName' => $request->input('lastName'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'servicesNeeded' => json_encode($serviceIds),
            'additionalInfo' => $request->input('additionalInfo'),
            'status' => $request->input('status'),
            'agreedToTerms' => $request->input('agreedToTerms'),
        ]);

        $this->clearQuoteCache($shop);

        return ApiResponse::success(['quote' => $quote], 'Job Quote updated successfully');
    }

    public function destroy(string $shop_uuid, int $id): JsonResponse
    {
        $shop = $this->getShopByUuid($shop_uuid);
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $quote = Quote::where('shop_id', $shop->id)->findOrFail($id);
        $quote->delete();

        $this->clearQuoteCache($shop);

        return ApiResponse::success([], 'Quote deleted successfully');
    }

    public function clearQuoteCache(Shop $shop): void
    {
        (new ClearCache)->clear('pending_quotes_page_', $shop->id);
    }

    public function subscribe(string $email, string $shop_uuid): bool
    {
        $emailHash = (new EncryptEmail)->checkIfSubscriberExists($email);

        $shop = $this->getShopByUuid($shop_uuid);
        if (! $shop) {
            return false;
        }

        if (! empty($emailHash['email_hash']) && ! empty($emailHash['encrypted_email'])) {
            Subscriber::create([
                'shop_id' => $shop->id,
                'email' => $emailHash['encrypted_email'],
                'email_hash' => $emailHash['email_hash'],
                'options' => ['Promotional', 'Launching', 'Announcement'],
                'opt_in' => true,
            ]);

            return true;
        }

        return false;
    }

    private function getShopByUuid(string $uuid): ?Shop
    {
        return Shop::where('uuid', $uuid)->first();
    }
}
