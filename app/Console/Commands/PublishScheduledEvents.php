<?php

namespace App\Console\Commands;

use App\Mail\NewsletterMail;
use App\Models\BusinessEvent;
use App\Models\Subscribe;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class PublishScheduledEvents extends Command
{
    protected $signature = 'app:publish-scheduled-events';
    protected $description = 'Publish business events when their start time is reached';

    public function handle()
    {
        $now = Carbon::now();

        $businessEvents = BusinessEvent::where('status', 'scheduled')
        ->where('visible', 1)
        ->get();

        foreach ($businessEvents as $businessEvent) {
            $startDate = Carbon::parse($businessEvent->start_date);
            $endDate = Carbon::parse($businessEvent->end_date);

            // Option A: Only if now >= start date
            // if ($now->greaterThanOrEqualTo($startDate)) {

            // Option B: If now is within 5 minutes of start_date
            if ($now->between($startDate->copy()->subMinutes(5), $startDate->copy()->addMinutes(5))) {
                $businessEvent->status = 'published';
                $businessEvent->published_at = $now;
                $businessEvent->save();

                $this->sendNewsletter($businessEvent);

                $this->info("Published and sent newsletter: {$businessEvent->title}");
            }

            if ($now->greaterThanOrEqualTo($endDate)) {
                $businessEvent->status = 'scheduled';
                $businessEvent->visible = 0;
                $businessEvent->save();

                $this->info("Archived: {$businessEvent->title}");
            }
        }

        $this->info('Finished checking scheduled events.');
    }

    private function sendNewsletter($businessEvent)
    {
        $subscribers = Subscribe::where('opt_in', true)->get();

        foreach ($subscribers as $subscriber) {
            $decryptedEmail = Crypt::decryptString($subscriber->email);
            
            Mail::to($decryptedEmail)->send(new NewsletterMail($businessEvent));
            
            sleep(2);
        }
    }
}
