<?php

namespace SunAppModules\SunBet\Http\Controllers\Api;

use SunAppModules\SunBet\Entities\SunbetCompetition;
use SunAppModules\SunBet\Http\Resources\ScheduleResource;
use SunAppModules\SunBet\Http\Resources\StandingsCollection;
use SunAppModules\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SunAppModules\SunBet\Entities\SunbetStanding;

class StandingsController extends Controller
{
    public function index(Request $request)
    {
        $standings = SunbetStanding::with('teams')->get();
        $competitions = SunbetCompetition::all();
        foreach ($competitions as $status) {
            if ($status->status && $standings) {
                return new StandingsCollection($standings);
            } else {
                return [
                    'data' => [
                        'status' => 'failed',
                        'error' => 404,
                    ]
                ];
            }
        }
    }

    public function show($id, Request $request)
    {
        $standing = SunbetStanding::find($id);
        $competitions = SunbetCompetition::all();
        foreach ($competitions as $status) {
            if ($status->status && $standing) {
                return new ScheduleResource($standing->teams);
            }

            return [
                'data' => [
                    'status' => 'failed',
                    'error' => 404,
                ]
            ];
        }
    }
}
