<?php

namespace SunAppModules\SunBet\Console;

use GuzzleHttp\Client;
use SunAppModules\SunBet\Entities\SunbetCompetition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use SunAppModules\SunBet\Entities\SunbetPlayer;

class FetchPlayersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'players:fetch {code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all players from footballdata-org.com API';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $progressbar = $this->output->createProgressBar();
        $progressbar->start();

        $client = new Client();
        $response = json_decode($client->request('GET',
            'https://api.football-data.org/v4/competitions/' . $this->argument('code') . '/teams',
            [
                'headers' => [
                    'X-Auth-Token' => 'eb39c4511bf64a388e73dc566a8a99cd'
                ]
            ])->getBody()->getContents());


        if (!property_exists($response, 'sunbet_players')) {
            foreach ($response->teams as $team) {
//                SunbetPlayer::updateOrCreate(
//                    [
//                        'team_id' => $team->id,
//                    ],
//                );
                foreach ($team->squad as $players) {
                    SunbetPlayer::updateOrCreate(
                        [
                            'id'=> $players->id ?? null,
                        ],
                        [
                        'team_id' => $team->id,
                        'name' => $players->name ?? null,
                        'position'=> $players->position ?? null,
                        'dateOfBirth'=> $players->dateOfBirth ?? null,
                        'nationality'=> $players->nationality ?? null,
                        ]
                );
                    $progressbar->advance();
                }
            }
        } else {
            dump($response);
        }
                    $progressbar->finish();

        return 0;
    }
}
