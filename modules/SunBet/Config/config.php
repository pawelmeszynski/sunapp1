<?php

return [
    'name' => 'SunBet',
    'auth' => [
        'guards' => [
            'sunbet_api' => [
                'driver' => 'passport',
                'provider' => 'sunbet_users',
            ],
        ],
    ],
    'github' => [
        'client_id' => env('GITHUB_ID'),
        'client_secret' => env('GITHUB_SECRET'),
        'redirect' => env('GITHUB_URL'),
    ],
    'providers' => [
        Laravel\Socialite\SocialiteServiceProvider::class,
    ],
    'Socialite' => Laravel\Socialite\Facades\Socialite::class,

];
