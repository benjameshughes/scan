<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class LinnworksApiService
{
    protected $app_id;
    protected $app_secret;
    protected $app_token;
    protected string $base_url;
    protected string $auth_url;
    protected $session_token;
    private $client;
    public function __construct()
    {
        $this->client = new Client();
        $this->app_id = config('linnworks.app_id');
        $this->app_secret = config('linnworks.app_secret');
        $this->app_token = config('linnworks.app_token');
        $this->base_url = config('linnworks.base_url');
        $this->auth_url = config('linnworks.auth_url');

        if (!Cache::has('linnworks.session_token')) {
            $this->checkAndAuthorize();
        }
    }

    // Helper function to check the session token and refresh it if necessary
    private function checkAndAuthorize()
    {
        // Check if the session token is expired or missing
        if (!Cache::has('linnworks.session_token')) {
            // Refresh the session token by calling the authorization function
            $this->authorizeByApplication();
        }

        return $this->session_token = Cache::get('linnworks.session_token');
    }

    public function authorizeByApplication()
    {
        // Let's check to see if we have a session token or not so we don't have to re-authenticate
        if (Cache::has('linnworks.session_token')) {
            return $this->session_token = Cache::get('linnworks.session_token');
        }

        $body = json_encode([
            "ApplicationId" => $this->app_id,
            "ApplicationSecret" => $this->app_secret,
            "Token" => $this->app_token,
        ]);

        $response = $this->client->request('POST', $this->auth_url . 'Auth/AuthorizeByApplication', [
            'body' => $body,
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
        ]);

        // Check if the response is successful (status code 200)
        if ($response->getStatusCode() === 200) {
            // Decode the response body to retrieve the token
            $responseBody = json_decode($response->getBody()->getContents(), true);
            $session_token = $responseBody['Token'];

            // Store the session token in the cache for later use (expires in 60 minutes)
            Cache::put('linnworks.session_token', $session_token, now()->addMinutes(60));
            Log::channel('lw_auth')->info('Authorized by application');

            // Return the session token
            return $session_token;
        }

        // If the response was not successful, log the error and throw an exception
        Log::channel('lw_auth')->error('Authorization failed: ' . $response->getBody()->getContents());
        throw new Exception('Unable to authorize by application');
    }

    public function updateStockLevel(string $sku, int $quantity)
    {
        $body = json_encode([
            'stockLevels' => [
                [
                    'SKU' => $sku,
                    'LocationId' => '00000000-0000-0000-0000-000000000000',
                    'Level' => $quantity, // Ensure $quantity is an integer
                ]
            ]
        ]);

        $response = $this->client->request('POST', $this->base_url . 'Stock/SetStockLevel', [
            'body' => $body,
            'headers' => [
                'Authorization' => Cache::get('linnworks.session_token'),
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody());
    }

    // Get stock level
    public function getStockLevel(string $sku = '')
    {
        $body = json_encode([
            'keyword' => trim($sku),
            'loadCompositeParents' => false,
            'loadVariationParents' => false,
            'entriesPerPage' => 1,
            'pageNumber' => 1,
            "dataRequirements" => ["StockLevels"],
            "searchTypes" => ["SKU","Title","Barcode"],
        ]);

        $response = $this->client->request('POST', $this->base_url . 'Stock/GetStockItemsFull', [
            'body' => $body,
            'headers' => [
                'Authorization' => Cache::get('linnworks.session_token'),
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
        ]);

        $data = json_decode($response->getBody());

        return $data[0]->StockLevels[0]->StockLevel;
    }

}