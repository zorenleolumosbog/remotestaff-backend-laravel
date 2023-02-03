<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'facebook' => [
        'client_id' => '2302784033265705',
        'client_secret' => '1e42884cbd4502b0b51a7080e4fc6e1d',
        'redirect' => ''
    ],

    'twitter' => [
        'client_id' => 'EEndxuv2dQeXZvVUWohygoks3',
        'client_secret' => 'bxEN9AkUSa1D3qotG54iRMkxeYyePsHNU5y9JdeX7vaCiCEUYl',
        'redirect' => ''
    ],

    'instagram' => [
        'client_id' => '419250520421209',
        'client_secret' => '6e4f6659ce87c21fd404b1f5dd85547e',
        'redirect' => ''
    ],

    'linkedin' => [
        'client_id' => '786d0ivjai5oyh',
        'client_secret' => 'xYh7JShQjh03cjnZ',
        'redirect' => ''
    ],

    'google' => [
        'client_id' => '372459714707-19f3coi08hpqfubo8it4rt2p11apbh6q.apps.googleusercontent.com',
        'client_secret' => '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',
        'redirect' => ''
    ],

];
