<?php

namespace Showcase\Http\Controllers\Edit;

use Showcase\Models\Batch;
use Showcase\Models\Event;
use Showcase\Models\Photo;
use Illuminate\Http\Request;
use Showcase\Services\Photos;

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
    public function store(Request $request, Batch $batch, Event $event)
    {
        $event = Event::GetFromBatchNameAndWebname($batch->Webname, $event->Webname);
        Photos::Host($request->file('file'), function($urls) use($event) {
            $photo = new \Showcase\Models\Photo;
            $photo->Url         = $urls['o'];
            $photo->UrlLarge    = $urls['l'];
            $photo->UrlMedium   = $urls['m'];
            $photo->UrlSmall    = $urls['s'];
            $photo->EventId     = $event->Id;
            $photo->save();
        });

        return $this->Ok();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Showcase\Models\Photo  $photo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Photo $photo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Showcase\Models\Batch    $batch
     * @param  \Showcase\Models\Event    $event
     * @param  \Showcase\Models\Photo  $photo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Batch $batch, Event $event, Photo $photo)
    {
        $event = Event::GetFromBatchNameAndWebname($batch->Webname, $event->Webname);
        $photo->delete();
        return $this->Ok();
    }
}
