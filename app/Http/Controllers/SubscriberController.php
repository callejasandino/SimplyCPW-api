<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\EncryptEmail;
use App\Models\Subscriber;

class SubscriberController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'options' => 'nullable|array',
        ]);

        $emailHash = (new EncryptEmail())->checkIfSubscriberExists($validated['email']);

        $options = $request->input('options');

        if (isset($emailHash['email_hash']) && isset($emailHash['encrypted_email'])) {
            Subscriber::create([
                'email' => $emailHash['encrypted_email'],
                'email_hash' => $emailHash['email_hash'],
                'options' => $options ? $options : ['Promotional', 'Announcement', 'Launching'],
                'opt_in' => true
            ]);

            return response()->json([
                'message' => 'You have been subscribed to the newsletter',
            ], 200);
        } else if (!empty($emailHash['saved_email_hash'])) {
            $subscribe = Subscriber::where('email_hash', $emailHash['saved_email_hash'])->first();

            if ($subscribe) {
                $subscribe->update([
                    'opt_in' => true,
                    'options' => $options ? $options : $subscribe->options
                ]);
            }

            return response()->json([
                'message' => 'You have been subscribed to the newsletter',
            ], 200);
        }
    }

    public function unsubscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $emailHash = (new EncryptEmail())->checkIfSubscriberExists($validated['email']);

        if (isset($emailHash['saved_email_hash']) && !empty($emailHash['saved_email_hash'])) {   
            $subscribe = Subscriber::where('email_hash', $emailHash['saved_email_hash'])->first();

            if ($subscribe) {
                $subscribe->update([
                    'opt_in' => false
                ]);
            }

            return response()->json([
                'message' => 'You have been unsubscribed from the newsletter',
                'email' => $validated['email'],
            ], 200);
        } else {
            return response()->json([
                'message' => 'You are not subscribed to the newsletter',
            ], 400);
        }
    }
}
