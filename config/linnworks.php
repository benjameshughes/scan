<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Credentials
    |--------------------------------------------------------------------------
    |
    | These are the credentials used to authenticate with the Linnworks API.
    |
    */

    'app_id' => env('LINNWORKS_CLIENT_ID'),
    'app_secret' => env('LINNWORKS_CLIENT_SECRET'),
    'app_token' => env('LINNWORKS_APP_TOKEN'),

    /*
     |--------------------------------------------------------------------------
     | Base URL
     |--------------------------------------------------------------------------
     |
     | This is the base URL used to access the Linnworks API.
     |
     */

    'auth_url' => env('LINNWORKS_AUTH_URL', 'https://api.linnworks.net/api/'),
    'base_url' => env('LINNWORKS_BASE_URL', 'https://eu-ext.linnworks.net/api/'),
];
