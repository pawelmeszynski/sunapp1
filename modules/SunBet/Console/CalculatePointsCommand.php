<?php

namespace SunAppModules\SunBet\Console;

use Illuminate\Console\Command;
use SunAppModules\SunBet\Entities\SunbetPredict;
use SunAppModules\SunBet\Entities\SunbetSchedule;

class CalculatePointsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'points:calculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate points';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $schedules = SunbetSchedule::where('points_calculated', false)->where('status', 'FINISHED')->where('competition_id', 2000)->get();

        $schedules->each(function (SunbetSchedule $schedule) {
            $schedule->predicts->each(function (SunbetPredict $predict) use ($schedule) {
                $winnerIs = $schedule->home - $schedule->away; //-1 means away wins
                $isDraw = $schedule->home == $schedule->away;

                $userPredictedDraw = $predict->home_team_goals == $predict->away_team_goals;
                $predictedWinner = $predict->home_team_goals - $predict->away_team_goals; //-1 means away wins

                if ($schedule->home == $predict->home_team_goals && $schedule->away == $predict->away_team_goals) {
                    $predict->user->increment('points', 3);
                } else if ($isDraw && $userPredictedDraw) {
                    $predict->user->increment('points', 1);
                } else if (($winnerIs < 0 && $predictedWinner < 0) || ($winnerIs > 0 && $predictedWinner > 0)) {
                    $predict->user->increment('points', 1);
                }
            });

            $schedule->update([
                'points_calculated' => true,
            ]);
        });
    }
}
