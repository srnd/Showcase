<?php

namespace Showcase\Http\Middleware;

use Illuminate\Http\Request;
use Showcase\Models\Event;

class ShareSession
{
    public function handle(Request $request, \Closure $next)
    {
        \View::share('username', session()->get('username'));
        \View::share('version', trim(@file_get_contents(dirname(dirname(dirname(dirname(__FILE__)))).'/.version')));

        $params = \Route::current()->parameters();
        $event = $params['event'] ?? null;
        if (is_string($event)) $event = Event::GetFromBatchNameAndWebname($params['batch'], $params['event']);

        if ($request->session()->get('is_admin')) {
            \View::share('can_edit', true);
            \View::share('is_admin', true);
        } else if ($event && $request->session()->has('managed_events')
            && in_array($event->Id, $request->session()->get('managed_events'))) {
            \View::share('can_edit', true);
            \View::share('is_admin', false);
        } else {
            \View::share('can_edit', false);
            \View::share('is_admin', false);
        }

        return $next($request);
    }
}
