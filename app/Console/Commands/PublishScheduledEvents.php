<?php

namespace App\Console\Commands;

use App\Jobs\SendNewsletterEmail;
use App\Mail\NewsletterMail;
use App\Models\BusinessEvent;
use App\Models\Subscriber;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PublishScheduledEvents extends Command
{
    protected $signature = 'app:publish-scheduled-events';
    protected $description = 'Publish business events when their start time is reached';

    public function handle()
    {
        $now = Carbon::now(); // assumes config('app.timezone') is Asia/Manila

        $businessEvents = BusinessEvent::where('visible', 1)
            ->get();

        foreach ($businessEvents as $businessEvent) {
            $startDate = Carbon::parse($businessEvent->start_date);
            $endDate = Carbon::parse($businessEvent->end_date);

            $startWindow = $startDate->copy()->subMinutes(10);
            $endWindow = $startDate->copy()->addMinutes(10);

            $this->info("Checking event: {$businessEvent->title}");
            $this->info("Now: {$now->format('Y-m-d H:i:s')}, Start: {$startDate->format('Y-m-d H:i:s')}, Window: {$startWindow->format('Y-m-d H:i:s')} to {$endWindow->format('Y-m-d H:i:s')}");

            if ($businessEvent->status === 'scheduled' && $now->between($startWindow, $endWindow)) {
                $businessEvent->status = 'published';
                $businessEvent->save();

                $this->sendNewsletter($businessEvent, $businessEvent->event_type);

                $this->info("Published and sent newsletter: {$businessEvent->title}");
            }

            if ($businessEvent->status === 'published' && $now->greaterThanOrEqualTo($endDate)) {
                $businessEvent->status = 'archived';
                $businessEvent->visible = 0;
                $businessEvent->save();

                $this->info("Archived: {$businessEvent->title}");
            }
        }

        $this->info('Finished checking scheduled events.');
    }




    private function sendNewsletter($businessEvent, $eventType)
    {
        $subcribers = Subscriber::where('opt_in', true)->whereJsonContains('options', $eventType)->get();

        Bus::batch(
            $subcribers->map(fn ($subcriber) => new SendNewsletterEmail($subcriber->email, $businessEvent))
        )->then(function (Batch $batch) {
            // All jobs completed successfully
            Log::info('Newsletter batch completed!', ['batch_id' => $batch->id]);
        })
        ->catch(function (Throwable $e) {
            // One or more jobs failed
            Log::error('Newsletter batch failed.', ['error' => $e->getMessage()]);
        })
        ->finally(function () {
            // Always executed at the end
            Log::info('Newsletter batch has finished processing.');
        })
        ->dispatch();
    }
}
