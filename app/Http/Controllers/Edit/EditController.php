<?php

namespace Showcase\Http\Controllers\Edit;

use Showcase\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;

class EditController extends Controller
{
    public function Ok()
    {
        if (Request::ajax() || Request::wantsJson()) {
            return json_encode(['status' => 200]);
        } else {
            return redirect()->back();
        }
    }
}
