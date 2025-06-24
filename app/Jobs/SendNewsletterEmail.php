<?php

namespace App\Jobs;

use App\Mail\NewsletterMail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;

class SendNewsletterEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $email;
    protected $businessEvent;

    public function __construct($email, $businessEvent)
    {
        $this->email = $email;
        $this->businessEvent = $businessEvent;
    }

    public function handle(): void
    {
        $decryptedEmail = Crypt::decryptString($this->email);

        Mail::to($decryptedEmail)->send(new NewsletterMail($this->businessEvent));
    }
}
