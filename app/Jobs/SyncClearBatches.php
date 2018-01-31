<?php

namespace Showcase\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Showcase\Services\Clear;
use Showcase\Models\Batch;
use Carbon\Carbon;

class SyncClearBatches implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach (Clear::GetBatches() as $batch) {
            // Sync Batch
            $batchModel = Batch::where('Id', '=', $batch->id)->first();
            if (!isset($batchModel)) {
                $batchModel = new Batch;
                $batchModel->Id = $batch->id;
            }
            $starts = new Carbon($batch->starts_at);
            $starts->addHours(12);

            $batchModel->Name = $batch->name;
            $batchModel->Webname = strtolower(str_replace(' ', '-', $batch->name));
            $batchModel->StartsAt = $starts;
            $batchModel->save();

            // Sync events
            //if ($batch->is_loaded) // TODO(@tylermenezes): Re-enable when syncing registrations
            SyncClearEvents::dispatch($batch->id);
        }
    }
}
