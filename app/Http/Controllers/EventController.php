<?php

namespace Showcase\Http\Controllers;

use Showcase\Models;
use Showcase\Services;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct()
    {
        if (\Route::current()) {
            $params = \Route::current()->parameters();
            $this->event = Models\Event::GetFromBatchNameAndWebname($params['batch'], $params['event']);
            \View::share('batch', $this->event->Batch);
            \View::share('event', $this->event);
        }
    }

    public function GetIndex()
    {
        $r = ['batch' => $this->event->batch, 'event' => $this->event];

        if      (!$this->event->IsInProgress)               return redirect()->route('event.wrapup', $r);
        elseif  (!$this->event->AreTeamsFormed)             return redirect()->route('event.ideas', $r);
        elseif  (!$this->event->ArePresentationsStarting)   return redirect()->route('event.teams', $r);
        else                                                return redirect()->route('event.presentations', $r);
    }

    public function GetIdeas()
    {
        return view('ideas', ['types' => Models\Idea::Types]);
    }

    public function GetTeams()
    {
        return view('teams', ['types' => Models\Idea::Types]);
    }

    public function GetPhotos()
    {
        return view('photos');
    }

    public function GetPresentations()
    {
        return view('presentations');
    }

    public function PostShuffle()
    {
        $order = range(0, count($this->event->PresentingTeams) - 1);
        shuffle($order);
        foreach ($this->event->PresentingTeams as $team) {
            $team->Sort = array_pop($order);
            $team->save();
        }
        return redirect()->back();
    }

    public function PostOrder(Request $request)
    {
        foreach ($this->event->PresentingTeams as $team) {
            $team->Sort = $request->get($team->Id ?? 0);
            $team->save();
        }

        return json_encode(['status' => 200]);
    }

    public function GetWrapup()
    {
        return view('wrapup-event');
    }

    public function PostWrapup(Request $request)
    {
        $this->event->Thanks = $request->get('thanks');
        $this->event->Post = $request->get('post');
        $this->event->save();

        $request->session()->flash('success', __('common.status.saved'));
        return redirect()->back();
    }

    public function PostExport(Request $request)
    {
        Services\Photos::CreateZip($this->event, $request->get('email'));
        $request->session()->flash('success', __('wrapup.export.sent'));
        return redirect()->back();
    }
}
