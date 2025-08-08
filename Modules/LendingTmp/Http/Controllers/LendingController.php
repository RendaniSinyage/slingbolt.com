<?php

namespace Modules\LendingTmp\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LendingController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        // The view name 'lendingtmp::index' uses the module's lower-case name
        // as a namespace, which is the convention for nwidart/laravel-modules.
        return view('lendingtmp::index');
    }
}
