<?php

namespace SunAppModules\SunBet\Console;

use SunAppModules\SunBet\Entities\SunbetSchedule;
use GuzzleHttp\Client;
use SunAppModules\SunBet\Entities\SunbetCompetition;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchMatchesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matches:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetching official schedule of World Cup in Qatar from footballorg.com Api';

    /**
     * Execute the console command.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle()
    {
        $competitions = SunbetCompetition::all();
        foreach ($competitions as $sync) {
            if ($sync->sync) {
                $client = new Client();
                $response = json_decode($client->request('GET',
                    'https://api.football-data.org/v4/competitions/' . $sync->code . '/matches',
                    [
                        'headers' => [
                            'X-Auth-Token' => 'eb39c4511bf64a388e73dc566a8a99cd'
                        ]
                    ])->getBody()->getContents());
            } else {
                return back();
            }
        }
            if (!property_exists($response, 'sunbet_schedules')) {
                foreach ($response->matches as $match) {

                    SunbetSchedule::updateOrCreate(
                        [
                            'id' => $match->id,
                        ],
                        [
                            'competition_id' => $match->competition->id,
                            'home_team_id' => $match->homeTeam->id,
                            'away_team_id' => $match->awayTeam->id,
                            'utc_date' => Carbon::parse($match->utcDate)->toDateTimeString(),
                            'status' => $match->status,
                            'matchday' => $match->matchday,
                            'stage' => $match->stage,
                            'group' => $match->group,
                            'last_updated_at' => Carbon::parse($match->lastUpdated),
                            'home' => $match->score->fullTime->home ?? 0,
                            'away' => $match->score->fullTime->away ?? 0,
                        ]);
                }
            } else {
                dump($response);
            }
        }

}
