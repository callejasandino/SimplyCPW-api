<?php

namespace App\Helpers;

use App\Models\Subscriber;
use Illuminate\Support\Facades\Crypt;

class EncryptEmail
{
    public function checkIfSubscriberExists($email)
    {
        $emailHash = hash_hmac('sha256', $email, config('app.key'));

        $isSubscriberExisting = Subscriber::where('email_hash', $emailHash)->first();

        if (! $isSubscriberExisting) {
            return [
                'email_hash' => $emailHash,
                'encrypted_email' => Crypt::encryptString($email),
            ];
        } else {
            return [
                'saved_email_hash' => $isSubscriberExisting->email_hash,
            ];
        }
    }
}