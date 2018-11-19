<?php

namespace Showcase\Http\Controllers;

use Showcase\Models\Batch;
use Showcase\Models\Event;
use Showcase\Models\Photo;
use Illuminate\Http\Request;

class GlobalController extends Controller
{
    public function GetIndex(Request $request)
    {
        if ($request->session()->has('managed_events')) {
            $eventIds = array_unique($request->session()->get('managed_events'));
            $events = array_map(function($id) { return Event::where('Id', '=', $id)->first(); }, $eventIds);
            $events = array_filter($events);
            view()->share('my_events', $events);
        }

        return view('index', [
            'photo' => Photo::orderByRaw('RAND()')->with('event')->first(),
            'all_batches' => Batch::orderBy('StartsAt', 'Desc')->get(),
            'current_batch' => Batch::Current(),
            'all_regions' => Event::groupBy('region')->get(),
        ]);
    }

    public function GetBatch(Batch $batch)
    {
        return view('wrapup', ['events' => $batch->Events]);
    }

    public function GetRegion(string $region)
    {
        $events = Event
            ::select('events.*')
            ->join('batches', 'batches.Id', '=', 'events.BatchId')
            ->where('region', '=', $region)
            ->orderBy('batches.StartsAt', 'DESC')
            ->with('Photos')
            ->get();
        return view('wrapup', ['events' => $events]);
    }

    public function GetRegionJson(string $region)
    {
        $events = Event
            ::select('events.*')
            ->join('batches', 'batches.Id', '=', 'events.BatchId')
            ->where('region', '=', $region)
            ->orderBy('batches.StartsAt', 'DESC')
            ->with('Photos')
            ->get();
        return response(json_encode($events));
    }

    public function GetRegionPhotosJson(Request $request, string $region)
    {
        $photos = Photo
                    ::select(['photos.Url', 'photos.UrlLarge', 'photos.UrlMedium', 'photos.UrlSmall'])
                    ->join('events', 'photos.EventId', 'events.Id')
                    ->where('events.Region', '=', $region);

        if ($request->get('random')) {
            $photos->orderByRaw('RAND()')->limit($request->get('random'));
        }
        $photos = $photos->get();

        return response(json_encode($photos));
    }
}
