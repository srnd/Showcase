<?php

namespace Showcase\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Showcase\Services\Clear;
use Showcase\Models\Registration;

class SyncClearRegistrations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $eventId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $eventId)
    {
        $this->eventId = $eventId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach (Clear::GetRegistrationsForEvent($this->eventId) as $registration) {
            $registrationModel = Registration::where('Id', '=', $registration->id)->first();
            if (!isset($registrationModel)) {
                $registrationModel = new Registration;
                $registrationModel->Id = $registration->id;
            }

            $registrationModel->EventId     = $this->eventId;
            $registrationModel->FirstName   = $registration->first_name;
            $registrationModel->LastName    = $registration->last_name;
            $registrationModel->Email       = $registration->email;
            $registrationModel->save();
        }
    }
}
