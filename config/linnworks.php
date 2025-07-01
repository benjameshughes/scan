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

    /*
    |--------------------------------------------------------------------------
    | API Pagination Settings
    |--------------------------------------------------------------------------
    |
    | Configure default page sizes for different API endpoints.
    | Linnworks typically supports up to 200 items per page.
    |
    */
    'pagination' => [
        // Default page size for most operations
        'default_page_size' => 200,
        
        // Maximum page size allowed by Linnworks API
        'max_page_size' => 200,
        
        // Page size for inventory operations
        'inventory_page_size' => 200,
        
        // Page size for product search operations
        'search_page_size' => 200,
        
        // Page size for sync operations
        'sync_page_size' => 200,
        
        // Page size for manual sync operations
        'manual_sync_page_size' => 200,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Settings
    |--------------------------------------------------------------------------
    |
    | Configure delays and rate limiting to avoid overwhelming the API.
    |
    */
    'rate_limiting' => [
        // Delay between batch operations in microseconds (250ms = 250000)
        'batch_delay_microseconds' => 250000,
        
        // Delay between batch operations in milliseconds
        'batch_delay_ms' => 250,
        
        // Maximum concurrent requests (for future use)
        'max_concurrent_requests' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Stock History Settings
    |--------------------------------------------------------------------------
    */
    'stock_history' => [
        // Default page size for stock history requests
        'page_size' => 20,
        
        // Maximum entries to fetch in one request
        'max_entries' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        // Cache key for session tokens
        'session_token_key' => 'linnworks.session_token',
        
        // Token cache TTL in minutes
        'token_ttl_minutes' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Location ID
    |--------------------------------------------------------------------------
    |
    | The default Linnworks location ID for stock operations.
    |
    */
    'default_location_id' => '00000000-0000-0000-0000-000000000000',

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'logging' => [
        // Log channel for authentication operations
        'auth_channel' => 'lw_auth',
        
        // Log channel for inventory operations
        'inventory_channel' => 'inventory',
        
        // Enable detailed API request logging
        'log_requests' => env('LINNWORKS_LOG_REQUESTS', false),
    ],

];
