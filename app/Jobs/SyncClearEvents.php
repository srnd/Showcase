<?php

namespace Showcase\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Showcase\Services\Clear;
use Showcase\Models\Event;

class SyncClearEvents implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $batchId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $batchId)
    {
        $this->batchId = $batchId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach (Clear::GetEventsForBatch($this->batchId) as $event) {
            $eventModel = Event::where('Id', '=', $event->id)->first();
            if (!isset($eventModel)) {
                $eventModel = new Event;
                $eventModel->Id = $event->id;
            }

            $eventModel->BatchId    = $this->batchId;
            $eventModel->Region     = $event->region_id;
            $eventModel->Webname    = $event->webname;
            $eventModel->Name       = $event->region_name;
            $eventModel->Timezone   = $event->timezone;
            if (isset($event->venue) && isset($event->venue->name)) {
                $eventModel->VenueName = $event->venue->name;
            }

            $eventModel->save();

            // SyncClearRegistrations::dispatch($event->id);
            // TODO(@tylermenezes) enable registration syncing when we can add people to teams
        }
    }
}
