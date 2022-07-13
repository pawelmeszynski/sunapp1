<?php

namespace SunAppModules\SunBet\Console;

use GuzzleHttp\Client;
use SunAppModules\SunBet\Entities\SunbetArea;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use SunAppModules\SunBet\Entities\SunbetCompetition;

class FetchAreasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'areas:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all areas from football-data.org';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $client = new Client();
        $response = json_decode($client->request('GET', 'https://api.football-data.org/v4/areas', [
            'headers' => [
                'X-Auth-Token' => 'eb39c4511bf64a388e73dc566a8a99cd'
            ]
        ])->getBody()->getContents());

        if (property_exists($response, 'areas')) {
            foreach ($response->areas as $areas) {
                SunbetArea::updateOrCreate(
                    [
                        'id' => $areas->id,
                    ],
                    [
                        'id' => $areas->id,
                        'name' => $areas->name,
                        'countryCode' => $areas->countryCode,
                        'flag' => $areas->flag,
                        'parentAreaId' => $areas->parentAreaId,
                        'parentArea' => $areas->parentArea,
                    ]
                );
            }
        } else {
            dump($response);
        }
        return 0;
    }
}
