<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Broadcasting Defaults
    |--------------------------------------------------------------------------
    |
    | Here you may set the default broadcast connection which should get used
    | by the framework. Each connection includes a variety of options as
    | we will discuss within the configuration options for the driver.
    |
    */

    'default' => env('BROADCAST_CONNECTION', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure each of the broadcast connections that should
    | be used by your application. A sensible default configuration has
    | been provided, but you are free to modify it as needed.
    |
    | Supported Drivers: "pusher", "ably", "redis", "log", "null"
    |
    */

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true,
                'encrypted' => true,
            ],
        ],

        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];
