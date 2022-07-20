<?php

namespace SunAppModules\SunBet\Console;

use App\Models\SunbetStanding;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use SunAppModules\SunBet\Entities\SunbetCompetition;
use SunAppModules\SunBet\Entities\SunbetTeam;

class FetchTeamsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teams:fetch {code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetching all competitions from WC football.org api';

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
                    'https://api.football-data.org/v4/competitions/' . $this->argument('code') . '/teams',
                    [
                        'headers' => [
                            'X-Auth-Token' => 'eb39c4511bf64a388e73dc566a8a99cd'
                        ]
                    ])->getBody()->getContents());
            } else {
                return back();
            }
        }

        if (!property_exists($response, 'sunbet_teams')) {
            foreach ($response->teams as $team) {
                SunbetTeam::updateOrCreate(
                    [
                        'id' => $team->id,
                    ],
                    [
                        'id' => $team->id,
                        'name' => $team->name,
                        'shortName' => $team->shortName,
                        'tla' => $team->tla,
                        'crest' => $team->crest,
                        'address' => $team->address,
                        'website' => $team->website,
                        'founded' => $team->founded,
                        'clubColors' => $team->clubColors,
                        'venue' => $team->venue,
                    ]
                );
                foreach ($team->runningCompetitions as $comp) {
                    $competition = SunbetCompetition::find($comp->id);
                    if ($competition) {
                        $competition->teams()->syncWithoutDetaching($team->id);
                    }
                }
            }
        } else {
            dump($response);
        }


        return 0;
    }
}

