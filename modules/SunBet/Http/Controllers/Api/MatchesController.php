<?php

namespace SunAppModules\SunBet\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use SunAppModules\SunBet\Entities\SunbetCompetition;
use SunAppModules\SunBet\Http\Requests\StoreScoreRequest;
use SunAppModules\SunBet\Http\Resources\PredictResource;
use SunAppModules\SunBet\Http\Resources\PredictsCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SunAppModules\SunBet\Http\Resources\ScheduleResource;
use SunAppModules\SunBet\Http\Resources\SchedulesCollection;
use SunAppModules\SunBet\Entities\SunbetPredict;
use SunAppModules\SunBet\Entities\SunbetSchedule;

class MatchesController extends Controller
{
    public function index()
    {
        $competitions = SunbetCompetition::all();
        foreach ($competitions as $status) {
            if ($status->status) {
                return new SchedulesCollection(SunbetSchedule::orderBy('utc_date', 'Asc')
                    ->where('matchday', '!=', 'NULL')
                    ->where('competition_id', '=', '2000')
                    ->paginate(8));
            }
        }
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->except('_token');

        $result = SunbetPredict::create([
            'match_id' => $data['match_id'],
            'user_id' => Auth::user()->id ?? null,
            'home_team_goals' => $data['home_team_goals'],
            'away_team_goals' => $data['away_team_goals'],
        ]);

        return response()->json([
            'status' => true,
            'message' => "Predict succesfully added",
            'predict' => $result
        ], 200);
    }

    public function show($id)
    {

        $schedule = SunbetSchedule::find($id);
        $competitions = SunbetCompetition::all();
        foreach ($competitions as $status) {
            if ($status->status && $schedule) {
                return new ScheduleResource($schedule);
            }
            return [
                'data' => [
                    'status' => 'failed',
                    'error' => 404,
                ]
            ];
        }
    }

    public function predicts(): SchedulesCollection
    {
        $competitions = SunbetCompetition::all();
        foreach ($competitions as $status) {
            if ($status->status) {
                return new SchedulesCollection(SunbetPredict::paginate(2));
            }
        }
    }

    public function showPredict($id)
    {
        $predict = SunbetPredict::find($id);
        $competitions = SunbetCompetition::all();
        foreach ($competitions as $status) {
            if ($status->status && $predict) {
                return new ScheduleResource($predict);
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
