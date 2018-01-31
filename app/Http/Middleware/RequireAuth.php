<?php

namespace Showcase\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RequireAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $require
     * @return mixed
     */
    public function handle($request, Closure $next, $require = null)
    {
        $params = \Route::current()->parameters();

        $event = $params['event'] ?? null;
        if (is_string($event)) $event = Event::GetFromBatchNameAndWebname($params['batch'], $params['event']);

        // Verify object source
        if (isset($params['team']) && $params['team']->EventId != $event->Id) \abort(404);
        if (isset($params['idea']) && $params['idea']->EventId != $event->Id) \abort(404);
        if (isset($params['photo']) && $params['photo']->EventId != $event->Id) \abort(404);

        if (!$request->session()->get('is_admin') &&
            !($event && $request->session()->has('managed_events')
            && in_array($event->Id, $request->session()->get('managed_events'))))
            \abort(401);

        return $next($request);
    }
}
