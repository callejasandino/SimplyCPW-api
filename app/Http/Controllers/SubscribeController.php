<?php

namespace App\Http\Controllers;

use App\Models\Subscribe;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class SubscribeController extends Controller
{
    public function subscribe($email, $opt_in)
    {
        $encryptedEmail = Crypt::encryptString($email);

        $subscribe = Subscribe::where('email', $encryptedEmail)->first();

        if ($subscribe) {
            return false;
        } else {
            Subscribe::create([
                'email' => $encryptedEmail,
                'opt_in' => $opt_in
            ]);
        }

        return true;
    }

    public function unsubscribe($email)
    {
        $encryptedEmail = Crypt::encryptString($email);

        $subscribe = Subscribe::where('email', $encryptedEmail)->first();
        if ($subscribe) {
            $subscribe->update([
                'opt_in' => false
            ]);
        }

        return response()->json([
            'message' => 'You have been unsubscribed from the newsletter',
            'email' => $email,
        ]);
    }
}
