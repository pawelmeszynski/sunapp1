<?php

namespace SunAppModules\SunBet\Console;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use SunAppModules\SunBet\Entities\SunbetCompetition;

class FetchCompetitionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'competitions:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all accessible competitions from football-data.org Api';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $client = new Client();
        $response = json_decode($client->request('GET', 'https://api.football-data.org/v4/competitions', [
            'headers' => [
                'X-Auth-Token' => 'eb39c4511bf64a388e73dc566a8a99cd'
            ]
        ])->getBody()->getContents());


        if(!property_exists($response, 'sunbet_competitions')) {
            foreach ($response->competitions as $competitions) {
                SunbetCompetition::updateOrCreate(
                    [
                        'id' => $competitions->id,
                    ],
                    [
                        'id' => $competitions->id,
                        'name' => $competitions->name,
                        'code' => $competitions->code,
                        'type' => $competitions->type,
                        'emblem' => $competitions->emblem,
                        'plan' => $competitions->plan,
                        'area_id' => $competitions->area->id
                    ]
                );
            }
        }
        else
        {
            dump($response);
        }

        return 0;
    }
}
