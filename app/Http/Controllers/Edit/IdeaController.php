<?php

namespace Showcase\Http\Controllers\Edit;

use Showcase\Models\Batch;
use Showcase\Models\Event;
use Showcase\Models\Idea;
use Illuminate\Http\Request;

class IdeaController extends EditController
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
        $idea = new Idea;
        $idea->Type = $request->get('type') ?? 'other';
        $idea->Idea = $request->get('idea');
        $idea->EventId = $event->Id;
        $idea->save();

        return $this->Ok();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Showcase\Models\Batch    $batch
     * @param  \Showcase\Models\Event    $event
     * @param  \Showcase\Models\Idea  $idea
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Batch $batch, Event $event, Idea $idea)
    {
        $event = Event::GetFromBatchNameAndWebname($batch->Webname, $event->Webname);
        $idea->Type = $request->get('type') ?? $idea->Type;
        $idea->Idea = $request->get('idea');
        $idea->save();

        return $this->Ok();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Showcase\Models\Batch    $batch
     * @param  \Showcase\Models\Event    $event
     * @param  \Showcase\Models\Idea  $idea
     * @return \Illuminate\Http\Response
     */
    public function destroy(Batch $batch, Event $event, Idea $idea)
    {
        $event = Event::GetFromBatchNameAndWebname($batch->Webname, $event->Webname);
        $idea->delete();
        return $this->Ok();
    }
}
