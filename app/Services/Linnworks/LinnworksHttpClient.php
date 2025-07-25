<?php

namespace App\Services\Linnworks;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

/**
 * Handles HTTP requests to the Linnworks API
 *
 * This class is responsible for:
 * - Making authenticated HTTP requests
 * - Handling retries and token refresh
 * - Error handling and logging
 * - Request/response transformation
 */
class LinnworksHttpClient
{
    private Client $client;

    private LinnworksAuthenticator $authenticator;

    private string $baseUrl;

    public function __construct(LinnworksAuthenticator $authenticator)
    {
        $this->client = new Client;
        $this->authenticator = $authenticator;
        $this->baseUrl = config('linnworks.base_url');
    }

    /**
     * Make an authenticated API request to Linnworks
     */
    public function makeAuthenticatedRequest(string $method, string $endpoint, array $options = []): array
    {
        $token = $this->authenticator->getValidToken();

        // Add authorization header
        $options['headers'] = array_merge($options['headers'] ?? [], [
            'Authorization' => $token,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ]);

        try {
            return $this->makeRequest($method, $this->baseUrl.$endpoint, $options);
        } catch (Exception $e) {
            // If we get a 401, try to refresh the token and retry once
            if (str_contains($e->getMessage(), '401')) {
                Log::channel('lw_auth')->warning('Received 401 error, refreshing token and retrying request');

                // Clear and refresh token
                $this->authenticator->clearToken();
                $token = $this->authenticator->getValidToken();

                $options['headers']['Authorization'] = $token;

                return $this->makeRequest($method, $this->baseUrl.$endpoint, $options);
            }

            throw $e;
        }
    }

    /**
     * Make a raw API request (without authentication)
     */
    public function makeRequest(string $method, string $url, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $url, $options);

            $responseData = json_decode($response->getBody()->getContents(), true);

            // Handle cases where API returns non-JSON responses
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON response from Linnworks API');
            }

            return $responseData;
        } catch (GuzzleException $e) {
            Log::channel('lw_auth')->error("API request failed: {$method} {$url} - ".$e->getMessage());

            // Transform specific Linnworks error messages to user-friendly messages
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'No items found with given filter')) {
                throw new Exception('Product not found in Linnworks');
            }

            throw new Exception("API request failed: {$errorMessage}");
        }
    }

    /**
     * Make a GET request with authentication
     */
    public function get(string $endpoint, array $options = []): array
    {
        return $this->makeAuthenticatedRequest('GET', $endpoint, $options);
    }

    /**
     * Make a POST request with authentication
     */
    public function post(string $endpoint, array $data = [], array $options = []): array
    {
        $options['body'] = json_encode($data);

        return $this->makeAuthenticatedRequest('POST', $endpoint, $options);
    }

    /**
     * Make a PUT request with authentication
     */
    public function put(string $endpoint, array $data = [], array $options = []): array
    {
        $options['body'] = json_encode($data);

        return $this->makeAuthenticatedRequest('PUT', $endpoint, $options);
    }

    /**
     * Make a DELETE request with authentication
     */
    public function delete(string $endpoint, array $options = []): array
    {
        return $this->makeAuthenticatedRequest('DELETE', $endpoint, $options);
    }

    /**
     * Get a plain text response (for endpoints that don't return JSON)
     */
    public function getPlainText(string $endpoint): string
    {
        $token = $this->authenticator->getValidToken();

        try {
            $response = $this->client->request('GET', $this->baseUrl.$endpoint, [
                'headers' => [
                    'Authorization' => $token,
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
            ]);

            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            // If we get a 401, try to refresh the token and retry once
            if (str_contains($e->getMessage(), '401')) {
                Log::channel('lw_auth')->warning('Received 401 error, refreshing token and retrying request');

                $this->authenticator->clearToken();
                $token = $this->authenticator->getValidToken();

                $response = $this->client->request('GET', $this->baseUrl.$endpoint, [
                    'headers' => [
                        'Authorization' => $token,
                        'accept' => 'application/json',
                        'content-type' => 'application/json',
                    ],
                ]);

                return $response->getBody()->getContents();
            }

            throw new Exception('Failed to get plain text response: '.$e->getMessage());
        }
    }

    /**
     * Test API connectivity
     */
    public function testConnection(): bool
    {
        try {
            // Simple health check endpoint
            $this->get('');

            return true;
        } catch (Exception $e) {
            Log::channel('lw_auth')->error('API connection test failed: '.$e->getMessage());

            return false;
        }
    }
}
