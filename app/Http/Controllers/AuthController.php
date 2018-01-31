<?php

namespace Showcase\Http\Controllers;

use Showcase\Services\Clear;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function GetLogin(Request $request)
    {

        $s5 = new \s5\API(\Config::get('s5.token'), \Config::get('s5.secret'));
        $s5->RequireLogin();
        $request->session()->put('username', $s5->User->me()->is_admin);
        if ($s5->User->me()->is_admin) {
            $request->session()->put('is_admin', true);
        }
        $events = Clear::GetEventsWithAccess($s5->User->me()->username);
        $request->session()->put('managed_events', array_map(function($event) { return $event->id; }, $events));

        return redirect()->route('index');
    }

    public function GetLogout(Request $request)
    {
        $request->session()->forget('username');
        $request->session()->forget('managed_events');
        $request->session()->forget('is_admin');

        return redirect()->route('index');
    }
}
