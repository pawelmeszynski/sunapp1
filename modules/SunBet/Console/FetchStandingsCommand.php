<?php

namespace SunAppModules\SunBet\Console;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use SunAppModules\SunBet\Entities\SunbetCompetition;
use SunAppModules\SunBet\Entities\SunbetStanding;
use SunAppModules\SunBet\Entities\SunbetTeam;

class FetchStandingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'standings:fetch {code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch official group standings from football-data.org API';

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
                    'https://api.football-data.org/v4/competitions/' . $sync->code . '/standings',
                    [
                        'headers' => [
                            'X-Auth-Token' => 'eb39c4511bf64a388e73dc566a8a99cd'
                        ]
                    ])->getBody()->getContents());
            } else {
                return back();
            }
        }

        if (!property_exists($response, 'sunbet_standings')) {
            foreach ($response->standings as $standings) {
                $result = SunbetStanding::updateOrCreate(
                    [
                        'group' => $standings->group,
                        'competition_id' => $response->competition->id
                    ],
                    [
                        'stage' => $standings->stage,
                        'type' => $standings->type,
                    ]);

                foreach ($standings->table as $table) {
                    $team = SunbetTeam::find($table->team->id);
                    $team->standings()->syncWithoutDetaching([
                        $result->id => [
                            'position' => $table->position,
                            'played_Games' => $table->playedGames,
                            'form' => $table->form,
                            'won' => $table->won,
                            'draw' => $table->draw,
                            'lost' => $table->lost,
                            'points' => $table->points,
                            'goals_For' => $table->goalsFor,
                            'goals_Against' => $table->goalsAgainst,
                            'goal_Difference' => $table->goalDifference,
                        ],
                    ]);
                }
            }
        } else {
            dump($response);
        }

        return 0;
    }
}
