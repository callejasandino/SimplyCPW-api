<?php

namespace App\Repositories;

use App\Helpers\ApiResponse;
use App\Helpers\EncryptEmail;
use App\Interfaces\SubscribersInterface;
use App\Models\Shop;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscribersRepository implements SubscribersInterface
{
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'options' => 'nullable|array',
            'shop_uuid' => 'required|string|exists:shops,uuid',
        ]);

        $shop = Shop::where('uuid', $request->input('shop_uuid'))->first();
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $emailHash = (new EncryptEmail)->checkIfSubscriberExists($validated['email']);

        $options = $request->input('options');

        if (isset($emailHash['email_hash']) && isset($emailHash['encrypted_email'])) {
            Subscriber::create([
                'shop_id' => $shop->id,
                'email' => $emailHash['encrypted_email'],
                'email_hash' => $emailHash['email_hash'],
                'options' => $options ? $options : ['Promotional', 'Announcement', 'Launching'],
                'opt_in' => true,
            ]);

            return ApiResponse::success([], 'You have been subscribed to the newsletter', 200);
        } elseif (! empty($emailHash['saved_email_hash'])) {

            $emailHash['subscriber']->update([
                'opt_in' => true,
                'options' => $options ? $options : $emailHash['subscriber']->options,
            ]);

            return ApiResponse::success([], 'You have been subscribed to the newsletter', 200);
        }

        return ApiResponse::error('You are not subscribed to the newsletter', 400);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'shop_uuid' => 'required|string|exists:shops,uuid',
        ]);

        $shop = Shop::where('uuid', $request->input('shop_uuid'))->first();
        if (! $shop) {
            return ApiResponse::error('Shop not found', 404);
        }

        $emailHash = (new EncryptEmail)->checkIfSubscriberExists($validated['email']);

        if (isset($emailHash['saved_email_hash']) && ! empty($emailHash['saved_email_hash'])) {
            $emailHash['subscriber']->update([
                'opt_in' => false,
            ]);

            return ApiResponse::success([], 'You have been unsubscribed from the newsletter', 200);
        } else {
            return ApiResponse::error('You are not subscribed to the newsletter', 400);
        }
    }
}
