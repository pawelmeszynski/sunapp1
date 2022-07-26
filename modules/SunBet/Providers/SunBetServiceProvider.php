<?php

namespace SunAppModules\SunBet\Providers;

use Config;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;
use SunAppModules\Core\Providers\ModuleServiceProvider;
use SunAppModules\SunBet\Console\CalculatePointsCommand;
use SunAppModules\SunBet\Console\FetchAreasCommand;
use SunAppModules\SunBet\Console\FetchCompetitionsCommand;
use SunAppModules\SunBet\Console\FetchDataCommand;
use SunAppModules\SunBet\Console\FetchMatchesCommand;
use SunAppModules\SunBet\Console\FetchPlayersCommand;
use SunAppModules\SunBet\Console\FetchStandingsCommand;
use SunAppModules\SunBet\Console\FetchTeamsCommand;
use SunAppModules\SunBet\Entities\SunbetUser;

class SunBetServiceProvider extends ModuleServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */

    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];


//    /**
//     * @param string $state
//     *
//     * @return string
//     */
//    protected function getAuthUrl($state)
//    {
//        return $this->buildAuthUrlFromBase('sunapp1.ddev.site/authorize/', $state);
//    }
//
//    /**
//     * @return string
//     */
//    protected function getTokenUrl()
//    {
//        return 'sunapp1.ddev.site/oauth/token';
//    }
//
//    /**
//     * @param string $token
//     *
//     * @throws GuzzleException
//     *
//     * @return array|mixed
//     */
//    protected function getUserByToken($token)
//    {
//        $response = $this->getHttpClient()->post('sunapp1.ddev.site/dashboard', [
//            'headers' => [
//                'Authorization' => 'Bearer ' . $token,
//                'Content-Type' => 'application/json',
//            ],
//        ]);
//
//        return json_decode($response->getBody()->getContents(), true);
//    }
//
//    /**
//     * @return SunbetUser
//     */
//    protected function mapUserToObject(array $users)
//    {
//        return (new SunbetUser())->setRaw($users)->map([
//            'id' => $users['id'],
//            'email' => $users['email'],
//        ]);
//    }
    public function boot(Router $router, Kernel $kernel)
    {
        parent::boot($router, $kernel);
        $this->app->register(PassportServiceProvider::class);
        Passport::routes();
        Passport::personalAccessClientId('user_id');
        $startTime = date("Y-m-d H:i:s");
        $endTime = date("Y-m-d H:i:s", strtotime('+7 day +1 hour +30 minutes +45 seconds', strtotime($startTime)));
        $expTime = \DateTime::createFromFormat("Y-m-d H:i:s", $endTime);
        Passport::tokensExpireIn($expTime);

        Config::set('auth.providers.sunbet_users', ["driver" => "eloquent", "model" => SunbetUser::class]);
        Config::set('auth.guards.api', ["driver" => "passport", "provider" => "sunbet_users"]);
        Config::set(
            'auth.passwords.sunbet_users',
            [
                "provider" => "sunbet_users",
                "table" => "password_resets",
                "expire" => 10080
            ]
        );
        $this->commands(
            [
                FetchAreasCommand::class,
                FetchTeamsCommand::class,
                FetchCompetitionsCommand::class,
                FetchStandingsCommand::class,
                FetchDataCommand::class,
                FetchMatchesCommand::class,
                CalculatePointsCommand::class,
                FetchPlayersCommand::class,
            ]
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

}
