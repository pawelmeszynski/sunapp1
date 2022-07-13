<?php

namespace SunAppModules\SunBet\Console;

use GuzzleHttp\Client;
use SunAppModules\SunBet\Entities\SunbetCompetition;
use SunAppModules\SunBet\Entities\SunbetStanding;
use SunAppModules\SunBet\Entities\SunbetTeam;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

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
     * @return int
     */
    public function handle()
    {

//        $name = $this->choice(
//            'Which competitions you want to import?',
//            ['WC' => 'World Cup', 'EC'=> 'Euro Championship'],
//             0
//        );
//

        $client = new Client();
        $response = json_decode($client->request('GET',
            'https://api.football-data.org/v4/competitions/' . $this->argument('code') . '/standings',
            [
                'headers' => [
                    'X-Auth-Token' => 'eb39c4511bf64a388e73dc566a8a99cd'
                ]
            ])->getBody()->getContents());


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
