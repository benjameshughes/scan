<?php

namespace App\Services;

use App\Jobs\SyncBarcode;
use App\Models\Scan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SyncRetryService
{
    /**
     * Retry failed scans intelligently based on error type and failure patterns
     */
    public function retryFailedScans(array $options = []): array
    {
        $maxAge = $options['max_age_hours'] ?? 24; // Only retry scans from last 24 hours by default
        $errorTypes = $options['error_types'] ?? null; // Specific error types to retry
        $maxAttempts = $options['max_attempts'] ?? 5; // Don't retry scans that have failed too many times

        $query = Scan::where('sync_status', 'failed')
            ->where('created_at', '>=', Carbon::now()->subHours($maxAge))
            ->where('sync_attempts', '<', $maxAttempts);

        if ($errorTypes) {
            $query->whereIn('sync_error_type', $errorTypes);
        }

        $failedScans = $query->get();

        $retryStats = [
            'total_found' => $failedScans->count(),
            'queued_for_retry' => 0,
            'skipped' => 0,
            'by_error_type' => [],
        ];

        foreach ($failedScans as $scan) {
            $shouldRetry = $this->shouldRetrySpecificScan($scan);

            if ($shouldRetry) {
                $this->queueScanForRetry($scan);
                $retryStats['queued_for_retry']++;

                $errorType = $scan->sync_error_type ?? 'unknown';
                $retryStats['by_error_type'][$errorType] = ($retryStats['by_error_type'][$errorType] ?? 0) + 1;
            } else {
                $retryStats['skipped']++;
            }
        }

        Log::channel('inventory')->info('Bulk retry operation completed', $retryStats);

        return $retryStats;
    }

    /**
     * Check if a specific scan should be retried
     */
    public function shouldRetrySpecificScan(Scan $scan): bool
    {
        // Don't retry if already submitted
        if ($scan->submitted || $scan->sync_status === 'synced') {
            return false;
        }

        // Don't retry recent attempts (wait for cooldown)
        if ($scan->last_sync_attempt && $scan->last_sync_attempt->diffInMinutes(now()) < 5) {
            return false;
        }

        // Check error-specific retry rules
        $errorType = $scan->sync_error_type;

        $retryRules = [
            'rate_limit' => [
                'max_attempts' => 5,
                'min_wait_minutes' => 10,
            ],
            'network' => [
                'max_attempts' => 3,
                'min_wait_minutes' => 2,
            ],
            'timeout' => [
                'max_attempts' => 3,
                'min_wait_minutes' => 5,
            ],
            'api_error' => [
                'max_attempts' => 2,
                'min_wait_minutes' => 10,
            ],
            'product_not_found' => [
                'max_attempts' => 1,
                'min_wait_minutes' => 60, // Wait longer for manual intervention
            ],
            'auth' => [
                'max_attempts' => 1,
                'min_wait_minutes' => 30,
            ],
            'validation' => [
                'max_attempts' => 1,
                'min_wait_minutes' => 60,
            ],
        ];

        $rules = $retryRules[$errorType] ?? $retryRules['api_error'];

        // Check attempt count
        if ($scan->sync_attempts >= $rules['max_attempts']) {
            return false;
        }

        // Check wait time
        if ($scan->last_sync_attempt &&
            $scan->last_sync_attempt->diffInMinutes(now()) < $rules['min_wait_minutes']) {
            return false;
        }

        return true;
    }

    /**
     * Queue a scan for retry with appropriate delay
     */
    public function queueScanForRetry(Scan $scan): void
    {
        // Reset status to pending
        $scan->update(['sync_status' => 'pending']);

        // Calculate delay based on previous attempts and error type
        $delay = $this->calculateRetryDelay($scan);

        // Queue the job
        SyncBarcode::dispatch($scan)->delay(now()->addSeconds($delay));

        Log::channel('inventory')->info('Queued scan for retry', [
            'scan_id' => $scan->id,
            'error_type' => $scan->sync_error_type,
            'attempt' => $scan->sync_attempts,
            'delay_seconds' => $delay,
        ]);
    }

    /**
     * Calculate retry delay based on scan history
     */
    protected function calculateRetryDelay(Scan $scan): int
    {
        $errorType = $scan->sync_error_type ?? 'unknown';
        $attempts = $scan->sync_attempts;

        $baseDelays = [
            'rate_limit' => 300,    // 5 minutes
            'network' => 60,        // 1 minute
            'timeout' => 120,       // 2 minutes
            'api_error' => 180,     // 3 minutes
            'auth' => 600,          // 10 minutes
            'product_not_found' => 1800, // 30 minutes
            'validation' => 900,    // 15 minutes
            'unknown' => 300,       // 5 minutes
        ];

        $baseDelay = $baseDelays[$errorType] ?? $baseDelays['unknown'];

        // Exponential backoff with jitter
        $exponentialDelay = $baseDelay * (2 ** min($attempts, 4)); // Cap at 2^4

        // Add random jitter (±20%) to prevent thundering herd
        $jitter = $exponentialDelay * 0.2 * (mt_rand(-100, 100) / 100);

        return max(30, (int) ($exponentialDelay + $jitter)); // Minimum 30 seconds
    }

    /**
     * Get retry recommendations for administrators
     */
    public function getRetryRecommendations(): array
    {
        $recentFailures = Scan::where('sync_status', 'failed')
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->get()
            ->groupBy('sync_error_type');

        $recommendations = [];

        foreach ($recentFailures as $errorType => $scans) {
            $count = $scans->count();
            $avgAttempts = $scans->avg('sync_attempts');
            $retryableCount = $scans->filter(fn ($scan) => $this->shouldRetrySpecificScan($scan))->count();

            $recommendation = $this->getErrorTypeRecommendation($errorType, $count, $avgAttempts);

            $recommendations[] = [
                'error_type' => $errorType,
                'failed_count' => $count,
                'retryable_count' => $retryableCount,
                'avg_attempts' => round($avgAttempts, 1),
                'recommendation' => $recommendation,
                'priority' => $this->getRecommendationPriority($errorType, $count),
            ];
        }

        // Sort by priority
        usort($recommendations, fn ($a, $b) => $b['priority'] <=> $a['priority']);

        return $recommendations;
    }

    /**
     * Get specific recommendation for an error type
     */
    protected function getErrorTypeRecommendation(string $errorType, int $count, float $avgAttempts): string
    {
        return match ($errorType) {
            'rate_limit' => $count > 10 ?
                'High rate limit failures detected. Consider increasing sync delays or checking API quotas.' :
                'Some rate limit failures. Retries should resolve most cases.',

            'network' => $count > 20 ?
                'Significant network issues detected. Check connectivity to Linnworks servers.' :
                'Minor network issues. Retries should resolve most cases.',

            'auth' => $count > 5 ?
                'Authentication failures detected. Check Linnworks API credentials and token validity.' :
                'Few authentication errors. May resolve automatically.',

            'product_not_found' => $count > 0 ?
                'Product lookup failures. Review product catalog sync with Linnworks.' :
                'No product lookup issues.',

            'timeout' => $count > 15 ?
                'High timeout rates. Consider increasing timeout values or checking API performance.' :
                'Some timeout issues. Retries should help.',

            default => $count > 10 ?
                "High failure rate for {$errorType} errors. Manual investigation recommended." :
                "Low failure rate for {$errorType} errors. Monitor and retry as needed."
        };
    }

    /**
     * Get priority score for recommendations (higher = more urgent)
     */
    protected function getRecommendationPriority(string $errorType, int $count): int
    {
        $basePriority = match ($errorType) {
            'auth' => 100,              // Authentication issues are critical
            'product_not_found' => 80,  // Data integrity issues
            'rate_limit' => 60,         // Service limitations
            'network' => 50,            // Infrastructure issues
            'timeout' => 40,            // Performance issues
            default => 30               // Other issues
        };

        // Increase priority based on volume
        $volumeMultiplier = min(2.0, $count / 10); // Cap at 2x for high volume

        return (int) ($basePriority * (1 + $volumeMultiplier));
    }
}
