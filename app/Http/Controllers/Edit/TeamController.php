<?php

namespace Showcase\Http\Controllers\Edit;

use Showcase\Models\Batch;
use Showcase\Models\Event;
use Showcase\Models\Team;
use Illuminate\Http\Request;
use Showcase\Services\Photos;

class TeamController extends EditController
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
        $event = Event::GetFromBatchNameAndWebname($batch, $event);

        $team = new Team;
        $team->Name = $request->get('name');
        $team->Description = $request->get('description');
        $team->EventId = $event->Id;
        $team->save();
        if ($request->hasFile('photo')) {
            Photos::Host($request->file('photo'), function($urls) use ($team) {
                $team->PhotoUrl         = $urls['o'];
                $team->PhotoUrlLarge    = $urls['l'];
                $team->PhotoUrlMedium   = $urls['m'];
                $team->PhotoUrlSmall    = $urls['s'];
                $team->save();
            });
            $request->session()->flash('success', __('common.status.upload-processing'));
        } else {
            $request->session()->flash('success', __('common.status.saved'));
        }
        $team->save();
        return $this->Ok();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Showcase\Models\Batch    $batch
     * @param  \Showcase\Models\Event    $event
     * @param  \Showcase\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $batch, string $event, Team $team)
    {
        $event = Event::GetFromBatchNameAndWebname($batch, $event);

        $team->Name = $request->get('name') ?? $team->Name;
        $team->Description = $request->get('description') ?? $team->Description;
        $team->IsPresenting = $request->has('is_presenting') ? $request->get('is_presenting') : $team->IsPresenting;
        if ($request->get('is_presenting')) {
            $team->Sort = count($event->PresentingTeams) + 1;
        }
        if ($request->hasFile('photo')) {
            Photos::Host($request->file('photo'), function($urls) use ($team) {
                $team->PhotoUrl         = $urls['o'];
                $team->PhotoUrlLarge    = $urls['l'];
                $team->PhotoUrlMedium   = $urls['m'];
                $team->PhotoUrlSmall    = $urls['s'];
                $team->save();
            });
            $request->session()->flash('success', __('common.status.upload-processing'));
        } else {
            $request->session()->flash('success', __('common.status.saved'));
        }
        $team->save();
        return $this->Ok();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Showcase\Models\Team  $team
     * @param  \Showcase\Models\Batch    $batch
     * @param  \Showcase\Models\Event    $event
     * @return \Illuminate\Http\Response
     */
    public function destroy($batch, $event, Team $team)
    {
        $team->delete();
        return $this->Ok();
    }
}
