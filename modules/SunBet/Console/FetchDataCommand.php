<?php

namespace SunAppModules\SunBet\Console;

use SunAppModules\SunBet\Entities\SunbetArea;
use SunAppModules\SunBet\Entities\SunbetCompetition;
use App\Models\SunbetSchedule;
use App\Models\SunbetStanding;
use SunAppModules\SunBet\Entities\SunbetTeam;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

class FetchDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all data from football-data.org';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $progressbar = $this->output->createProgressBar(3);
        $progressbar->start();

        Artisan::call('areas:fetch');
            $progressbar->setMessage('Areas fetched');
            $progressbar->advance();

        Artisan::call('competitions:fetch');

            $progressbar->setMessage('Competitions fetched');
            $progressbar->advance();


        $competitions = SunbetCompetition::all('code');
        $competitions->each(function ($competition) {
            $exitCode = Artisan::call('teams:fetch ' . $competition->code);
            $exitCode = Artisan::call('matches:fetch ' . $competition->code);
            $exitCode = Artisan::call('standings:fetch ' . $competition->code);
            $exitCode = Artisan::call('players:fetch ' . $competition->code);
            sleep(60);
        });
        $progressbar->finish();
        return 0;
    }
}
