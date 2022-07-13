<?php

namespace SunAppModules\SunBet\Http\Controllers\Api;

use SunAppModules\SunBet\Http\Resources\ScheduleResource;
use SunAppModules\SunBet\Http\Resources\SchedulesCollection;
use SunAppModules\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SunAppModules\SunBet\Entities\SunbetCompetition;
use SunAppModules\SunBet\Entities\SunbetTeam;

class TeamsController extends Controller
{
    public function index(Request $request)
    {
        $competitions = SunbetCompetition::all();
        foreach ($competitions as $competition) {
            if ($competition->status) {
                return new SchedulesCollection($competition->teams);
            }
            return [
                'data' => [
                    'status' => 'failed',
                    'error' => 404,
                ]
            ];
        }
    }

    public function show($id, Request $request)
    {
        $team = SunbetTeam::find($id);
        $competitions = SunbetCompetition::all();
        foreach ($competitions as $status) {
            if ($status->status && $team) {
                return new ScheduleResource($team);
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
