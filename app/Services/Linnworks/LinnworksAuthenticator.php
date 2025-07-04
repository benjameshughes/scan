<?php

namespace App\Services\Linnworks;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Handles Linnworks API authentication and token management
 * 
 * This class is responsible for:
 * - Managing session tokens
 * - Token caching and validation
 * - Authentication with Linnworks API
 */
class LinnworksAuthenticator
{
    private Client $client;
    private string $appId;
    private string $appSecret;
    private string $appToken;
    private string $authUrl;
    private string $cacheKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->appId = config('linnworks.app_id');
        $this->appSecret = config('linnworks.app_secret');
        $this->appToken = config('linnworks.app_token');
        $this->authUrl = config('linnworks.auth_url');
        $this->cacheKey = config('linnworks.cache.session_token_key');
    }

    /**
     * Get a valid session token, ensuring it's fresh
     */
    public function getValidToken(): string
    {
        // Simply return the cached token if it exists
        if (Cache::has($this->cacheKey)) {
            return Cache::get($this->cacheKey);
        }

        // If no token in cache, get a new one
        Log::channel('lw_auth')->info('No token in cache, authorizing with Linnworks');

        return $this->refreshToken();
    }

    /**
     * Validate the cached token against a fresh one from the API
     * This method should be called from your scheduled task
     */
    public function validateCachedToken(): bool
    {
        Log::channel('lw_auth')->info('Validating Linnworks token');

        // Get the cached token
        $cachedToken = Cache::get($this->cacheKey);

        if (! $cachedToken) {
            Log::channel('lw_auth')->warning('No cached token found during validation');
            $this->refreshToken();

            return false;
        }

        try {
            // Get a fresh token from the API without updating the cache
            $freshToken = $this->getTokenFromApi();

            // Compare the tokens
            if ($freshToken === $cachedToken) {
                Log::channel('lw_auth')->info('Token validation successful - tokens match');

                return true;
            } else {
                Log::channel('lw_auth')->warning('Token mismatch detected, updating cached token');
                Cache::put($this->cacheKey, $freshToken);

                return false;
            }
        } catch (Exception $e) {
            Log::channel('lw_auth')->error('Token validation failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Force refresh the session token
     */
    public function refreshToken(): string
    {
        Log::channel('lw_auth')->info('Manually refreshing token');
        Cache::forget($this->cacheKey);

        return $this->authorizeByApplication();
    }

    /**
     * Check if we have a valid cached token
     */
    public function hasValidToken(): bool
    {
        return Cache::has($this->cacheKey);
    }

    /**
     * Get the current cached token (may be null)
     */
    public function getCachedToken(): ?string
    {
        return Cache::get($this->cacheKey);
    }

    /**
     * Clear the cached token
     */
    public function clearToken(): void
    {
        Cache::forget($this->cacheKey);
        Log::channel('lw_auth')->info('Cleared cached Linnworks token');
    }

    /**
     * Get a token from the API without caching it
     */
    private function getTokenFromApi(): string
    {
        $body = [
            'ApplicationId' => $this->appId,
            'ApplicationSecret' => $this->appSecret,
            'Token' => $this->appToken,
        ];

        try {
            $response = $this->client->request('POST', $this->authUrl.'Auth/AuthorizeByApplication', [
                'body' => json_encode($body),
                'headers' => [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            
            if (!isset($responseData['Token'])) {
                throw new Exception('Invalid response from Linnworks auth API');
            }

            return $responseData['Token'];
        } catch (GuzzleException $e) {
            Log::channel('lw_auth')->error('Failed to get token from API: '.$e->getMessage());
            throw new Exception('Unable to authenticate with Linnworks: '.$e->getMessage());
        }
    }

    /**
     * Authorize with Linnworks API and update the cache
     */
    private function authorizeByApplication(): string
    {
        try {
            $token = $this->getTokenFromApi();

            // Store the session token in the cache
            Cache::put($this->cacheKey, $token);
            Log::channel('lw_auth')->info('Authorized by application and updated cache');

            return $token;
        } catch (Exception $e) {
            Log::channel('lw_auth')->error('Authorization failed: '.$e->getMessage());
            throw new Exception('Unable to authorize by application: '.$e->getMessage());
        }
    }
}