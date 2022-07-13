<?php

namespace SunAppModules\SunBet\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SunAppModules\SunBet\Entities\SunbetUser;
use SunAppModules\SunBet\Http\Resources\ScheduleResource;
use SunAppModules\SunBet\Http\Resources\SchedulesCollection;

class UserStandingsController extends Controller
{
    public function index()
    {
        return new SchedulesCollection(SunbetUser::orderBy('points', 'desc')->get());
    }

    public function show($id)
    {
        $user = SunbetUser::find($id);

        if ($user) {
            return new ScheduleResource($user);
        }

        return [
            'data' => [
                'status' => 'failed',
                'error' => 404,
            ]
        ];
    }
}
