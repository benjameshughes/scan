<?php

namespace App\Actions\Concerns;

use App\Models\Scan;

trait UpdateScanStatus
{
    protected function updateScanStatus(Scan $scan, string $status): Scan
    {
        $scan->recordSyncAttempt($status);
        return $scan;
    }

    protected function markScanAsSuccessful(Scan $scan): Scan
    {
        $scan->update([
            'sync_status' => 'synced',
            'submitted' => true,
            'submitted_at' => now(),
            'synced_at' => now(),
            'sync_error_message' => null,
            'sync_error_type' => null,
        ]);

        return $scan;
    }

    protected function markScanAsSyncing(Scan $scan): Scan
    {
        $scan->recordSyncAttempt('syncing');
        return $scan;
    }

    protected function markScanAsFailed(Scan $scan, string $errorMessage = null, string $errorType = null, array $metadata = []): Scan
    {
        $scan->recordSyncAttempt('failed', $errorMessage, $errorType, $metadata);
        return $scan;
    }
    
    /**
     * Categorize error based on exception type or message
     */
    protected function categorizeError(\Throwable $exception): array
    {
        $errorMessage = $exception->getMessage();
        $exceptionClass = get_class($exception);
        
        // Network/HTTP errors
        if (str_contains($exceptionClass, 'GuzzleHttp') || 
            str_contains($exceptionClass, 'ConnectException') ||
            str_contains($errorMessage, 'Connection') ||
            str_contains($errorMessage, 'network')) {
            return ['network', 'Network Error: ' . $errorMessage];
        }
        
        // Authentication errors
        if (str_contains($exceptionClass, 'AuthenticationException') ||
            str_contains($errorMessage, 'auth') ||
            str_contains($errorMessage, 'token') ||
            str_contains($errorMessage, 'unauthorized')) {
            return ['auth', 'Authentication Error: ' . $errorMessage];
        }
        
        // Rate limiting
        if (str_contains($errorMessage, 'rate limit') ||
            str_contains($errorMessage, 'Too Many Requests') ||
            str_contains($errorMessage, '429')) {
            return ['rate_limit', 'Rate Limit Exceeded: ' . $errorMessage];
        }
        
        // Product not found
        if (str_contains($exceptionClass, 'NoSkuFoundException') ||
            str_contains($errorMessage, 'product not found') ||
            str_contains($errorMessage, 'SKU not found')) {
            return ['product_not_found', 'Product Not Found: ' . $errorMessage];
        }
        
        // Timeout errors
        if (str_contains($exceptionClass, 'TimeoutException') ||
            str_contains($errorMessage, 'timeout') ||
            str_contains($errorMessage, 'timed out')) {
            return ['timeout', 'Request Timeout: ' . $errorMessage];
        }
        
        // Validation errors
        if (str_contains($exceptionClass, 'ValidationException') ||
            str_contains($errorMessage, 'validation') ||
            str_contains($errorMessage, 'invalid')) {
            return ['validation', 'Validation Error: ' . $errorMessage];
        }
        
        // Generic API errors
        if (str_contains($errorMessage, 'API') || 
            str_contains($errorMessage, 'HTTP')) {
            return ['api_error', 'API Error: ' . $errorMessage];
        }
        
        // Default case
        return ['unknown', $errorMessage];
    }
    
    /**
     * Determine if an error should be retried based on type and attempt count
     */
    protected function shouldRetry(Scan $scan, string $errorType): bool
    {
        $maxAttempts = match($errorType) {
            'rate_limit' => 5,      // Rate limits often resolve quickly
            'network' => 3,         // Network issues can be transient
            'timeout' => 3,         // Timeouts can be retried
            'api_error' => 2,       // Generic API errors
            'auth' => 1,            // Auth errors usually need manual intervention
            'product_not_found' => 1, // Product issues need manual resolution
            'validation' => 1,      // Validation errors need fixing
            default => 2            // Conservative default
        };
        
        return $scan->sync_attempts < $maxAttempts;
    }
    
    /**
     * Get retry delay in seconds based on attempt count and error type
     */
    protected function getRetryDelay(int $attemptCount, string $errorType): int
    {
        $baseDelay = match($errorType) {
            'rate_limit' => 300,    // 5 minutes for rate limits
            'network' => 60,        // 1 minute for network issues
            'timeout' => 120,       // 2 minutes for timeouts
            default => 300          // 5 minutes default
        };
        
        // Exponential backoff: delay * (2 ^ (attempt - 1))
        return $baseDelay * (2 ** ($attemptCount - 1));
    }
}
