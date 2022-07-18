<?php

namespace SunAppModules\Core\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{
    /**
     * Show the system info.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function info()
    {
        return ['message' => config('app.name') . '::API - Powered by SunApp ' . app()::SUNAPP_VERSION];
    }

    /**
     * Show the logged user.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function loggedUser(Request $request)
    {
        return $request->user();
    }
}
