<?php

namespace Showcase\Http\Controllers\Edit;

use Showcase\Models\Batch;
use Showcase\Models\Event;
use Showcase\Models\Photo;
use Illuminate\Http\Request;
use Showcase\Services\Photos;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;

class PhotoController extends EditController
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Showcase\Models\Batch    $batch
     * @param  \Showcase\Models\Event    $event
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, string $batch, string $event)
    {
        $receiver = new FileReceiver("file", $request, HandlerFactory::classFromRequest($request));
        if (!$receiver->isUploaded()) abort(400);

        $save = $receiver->receive();

        if ($save->isFinished()) {
            $event = Event::GetFromBatchNameAndWebname($batch, $event);
            Photos::Host($save->getFile(), function($urls) use($event) {
                $photo = new \Showcase\Models\Photo;
                $photo->Url         = $urls['o'];
                $photo->UrlLarge    = $urls['l'];
                $photo->UrlMedium   = $urls['m'];
                $photo->UrlSmall    = $urls['s'];
                $photo->EventId     = $event->Id;
                $photo->save();
            });
        } else {
            $handler = $save->handler();

            return response()->json([
                "done" => $handler->getPercentageDone(),
            ]);
        }

        return $this->Ok();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Showcase\Models\Batch    $batch
     * @param  \Showcase\Models\Event    $event
     * @param  \Showcase\Models\Photo  $photo
     * @return \Illuminate\Http\Response
     */
    public function destroy($batch, $event, Photo $photo)
    {
        Photos::Delete($photo->Url);
        Photos::Delete($photo->UrlLarge);
        Photos::Delete($photo->UrlMedium);
        Photos::Delete($photo->UrlSmall);
        $photo->delete();
        return $this->Ok();
    }
}
