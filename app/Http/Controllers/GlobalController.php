<?php

namespace Showcase\Http\Controllers;

use Showcase\Models\Batch;
use Showcase\Models\Event;
use Showcase\Models\Photo;
use Illuminate\Http\Request;

class GlobalController extends Controller
{
    public function GetIndex()
    {
        $currentBatch = Batch::Current();
        if (isset($currentBatch)) return redirect()->route('batch', ['batch' => $currentBatch]);
        return view('index', ['all_batches' => Batch::get()]);
    }

    public function GetBatch(Batch $batch)
    {
        if      (!Batch::Current())     return view('wrapup', ['events' => $batch->Events]);
        else                            return view('batch', ['batch' => $batch]);
    }

    public function GetRegion(string $region)
    {
        return view('wrapup', ['events' => Event::where('region', '=', $region)->get()]);
    }

    public function GetRegionJson(string $region)
    {
        $events = Event::where('region', '=', $region)->with('Photos')->get();
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
