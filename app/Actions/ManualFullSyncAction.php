<?php

namespace App\Actions;

use App\Models\SyncProgress;
use App\Services\LinnworksApiService;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ManualFullSyncAction
{
    protected LinnworksApiService $linnworksService;

    protected DailyLinnworksSyncAction $syncAction;

    protected ?SyncProgress $progressTracker = null;

    public function __construct(LinnworksApiService $linnworksService, DailyLinnworksSyncAction $syncAction)
    {
        $this->linnworksService = $linnworksService;
        $this->syncAction = $syncAction;
    }

    /**
     * Execute a manual full sync with progress tracking
     */
    public function execute(bool $dryRun = false, ?string $sessionId = null): array
    {
        $startTime = microtime(true);
        $sessionId = $sessionId ?: Str::uuid()->toString();

        // Initialize progress tracking
        $this->progressTracker = SyncProgress::create([
            'session_id' => $sessionId,
            'user_id' => auth()->id(),
            'type' => 'manual_sync',
            'status' => 'running',
            'stats' => [
                'total_processed' => 0,
                'created' => 0,
                'queued' => 0,
                'errors' => 0,
                'batches_processed' => 0,
                'dry_run' => $dryRun,
            ],
            'current_operation' => 'Initializing sync...',
            'started_at' => now(),
        ]);

        $stats = [
            'total_processed' => 0,
            'created' => 0,
            'queued' => 0,
            'errors' => 0,
            'batches_processed' => 0,
            'execution_time' => 0,
            'dry_run' => $dryRun,
            'session_id' => $sessionId,
        ];

        // Generate random ATM effect timing (5-25 seconds total)
        $targetDuration = rand(5, 25); // seconds
        Log::info('Manual full sync started', ['dry_run' => $dryRun, 'session_id' => $sessionId, 'target_duration' => $targetDuration]);

        try {
            $page = 1;
            $batchSize = config('linnworks.pagination.manual_sync_page_size');
            $hasMorePages = true;

            // Get total count for progress calculation
            $this->updateProgress('Getting total product count from Linnworks...', $stats);
            $totalCount = $this->linnworksService->getInventoryCount();
            $estimatedBatches = ceil($totalCount / $batchSize);

            Log::info("Manual sync will process approximately {$totalCount} products in {$estimatedBatches} batches");

            // Calculate dynamic delays based on target duration
            $availableTime = $targetDuration - 1; // Reserve 1 second for final message
            $initDelay = min(1.5, $availableTime * 0.2); // 20% for initialization
            $perBatchDelay = max(0.1, ($availableTime * 0.8) / $estimatedBatches); // 80% distributed across batches

            Log::info('ATM effect timing calculated', [
                'target_duration' => $targetDuration,
                'init_delay' => $initDelay,
                'per_batch_delay' => $perBatchDelay,
                'estimated_batches' => $estimatedBatches,
            ]);

            // First, update progress with initial operation - add dynamic delay for UX confidence
            $this->updateProgress('Initializing sync process...', $stats, [
                'estimated_total_products' => $totalCount,
                'estimated_total_batches' => $estimatedBatches,
            ]);
            usleep($initDelay * 333333); // 1/3 of init delay

            $this->updateProgress('Connecting to Linnworks API...', $stats, [
                'estimated_total_products' => $totalCount,
                'estimated_total_batches' => $estimatedBatches,
            ]);
            usleep($initDelay * 333333); // 1/3 of init delay

            $this->updateProgress('Starting product synchronization...', $stats, [
                'estimated_total_products' => $totalCount,
                'estimated_total_batches' => $estimatedBatches,
            ]);
            usleep($initDelay * 333334); // Final 1/3 of init delay

            while ($hasMorePages) {
                $this->updateProgress("Processing batch {$page} (fetching {$batchSize} products)...", $stats, [
                    'current_batch' => $page,
                    'batch_size' => $batchSize,
                    'estimated_total_products' => $totalCount,
                    'estimated_total_batches' => $estimatedBatches,
                ]);

                Log::info("Processing batch {$page} (page size: {$batchSize})");

                // Add dynamic delay for UX confidence (ATM effect)
                usleep($perBatchDelay * 1000000); // Convert to microseconds

                // Get products from Linnworks
                $this->updateProgress("Fetching batch {$page} from Linnworks...", $stats, [
                    'current_batch' => $page,
                    'batch_size' => $batchSize,
                    'estimated_total_products' => $totalCount,
                    'estimated_total_batches' => $estimatedBatches,
                ]);

                $linnworksProducts = $this->linnworksService->getAllProducts($page, $batchSize);

                if (empty($linnworksProducts)) {
                    Log::info("No more products found on page {$page}, sync complete");
                    $this->updateProgress('No more products found. Finalizing sync...', $stats);
                    usleep(500000); // 0.5 seconds - Let users see finalization message
                    $hasMorePages = false;
                    break;
                }

                $productsInBatch = count($linnworksProducts);
                $this->updateProgress("Analyzing {$productsInBatch} products from batch {$page}...", $stats, [
                    'current_batch' => $page,
                    'products_in_batch' => $productsInBatch,
                    'estimated_total_products' => $totalCount,
                    'estimated_total_batches' => $estimatedBatches,
                ]);

                // Process this batch
                $batchStats = $this->syncAction->processBatch($linnworksProducts, $dryRun);

                // Aggregate stats
                $stats['total_processed'] += $batchStats['processed'];
                $stats['created'] += $batchStats['created'];
                $stats['queued'] += $batchStats['queued'];
                $stats['errors'] += $batchStats['errors'];
                $stats['batches_processed']++;

                Log::info("Batch {$page} completed", [
                    'batch_stats' => $batchStats,
                    'running_totals' => $stats,
                ]);

                $this->updateProgress("Batch {$page} completed. Processed: {$batchStats['processed']}, Created: {$batchStats['created']}, Queued: {$batchStats['queued']}", $stats, [
                    'current_batch' => $page,
                    'estimated_total_products' => $totalCount,
                    'estimated_total_batches' => $estimatedBatches,
                ]);

                // If we got fewer products than batch size, we're done
                if (count($linnworksProducts) < $batchSize) {
                    $hasMorePages = false;
                }

                $page++;

                // Add a small delay to avoid overwhelming the API
                if ($hasMorePages) {
                    $this->updateProgress('Waiting before next batch to avoid API rate limits...', $stats, [
                        'current_batch' => $page,
                        'estimated_total_products' => $totalCount,
                        'estimated_total_batches' => $estimatedBatches,
                    ]);
                    usleep(config('linnworks.rate_limiting.batch_delay_microseconds'));
                }
            }

            // Final completion message with delay
            $this->updateProgress('Sync completed successfully!', $stats);
            sleep(1); // Always 1 second for completion message

        } catch (Exception $e) {
            Log::error('Manual full sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'stats_so_far' => $stats,
            ]);

            $stats['errors']++;
            $stats['error_message'] = $e->getMessage();

            // Update progress with error
            if ($this->progressTracker) {
                $this->progressTracker->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'stats' => $stats,
                    'completed_at' => now(),
                ]);
            }
        }

        $stats['execution_time'] = round(microtime(true) - $startTime, 2);

        // Mark progress as completed
        if ($this->progressTracker && $this->progressTracker->status !== 'failed') {
            $this->progressTracker->update([
                'status' => 'completed',
                'stats' => $stats,
                'current_operation' => 'Sync completed successfully!',
                'completed_at' => now(),
            ]);
        }

        Log::info('Manual full sync completed', $stats);

        return $stats;
    }

    /**
     * Update progress tracking
     */
    private function updateProgress(string $operation, array $stats, array $batchInfo = []): void
    {
        if ($this->progressTracker) {
            $this->progressTracker->update([
                'stats' => $stats,
                'current_operation' => $operation,
                'current_batch' => $batchInfo,
            ]);
        }
    }

    /**
     * Get progress for a specific session
     */
    public function getProgress(string $sessionId): ?SyncProgress
    {
        return SyncProgress::where('session_id', $sessionId)
            ->where('user_id', auth()->id())
            ->first();
    }

    /**
     * Get estimated sync information without executing
     */
    public function getEstimatedInfo(): array
    {
        try {
            // Get actual total count from Linnworks
            $totalCount = $this->linnworksService->getInventoryCount();
            $batchSize = config('linnworks.pagination.manual_sync_page_size');
            $estimatedBatches = ceil($totalCount / $batchSize);

            return [
                'estimated_total' => number_format($totalCount),
                'total_count_raw' => $totalCount,
                'batch_size' => $batchSize,
                'estimated_batches' => number_format($estimatedBatches),
                'estimated_batches_raw' => $estimatedBatches,
                'last_sync' => $this->getLastSyncInfo(),
            ];

        } catch (Exception $e) {
            return [
                'error' => 'Could not connect to Linnworks API: '.$e->getMessage(),
                'last_sync' => $this->getLastSyncInfo(),
            ];
        }
    }

    /**
     * Get information about the last sync
     */
    private function getLastSyncInfo(): array
    {
        // Check when the daily sync command was last run
        // Read only the last 1000 lines to avoid memory issues
        $logPath = storage_path('logs/laravel.log');

        if (file_exists($logPath)) {
            try {
                // Use tail command to get last 1000 lines efficiently
                $command = 'tail -n 1000 '.escapeshellarg($logPath);
                $logs = shell_exec($command);

                if ($logs) {
                    // Look for recent sync log entries
                    if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*Daily Linnworks sync completed/', $logs, $matches)) {
                        return [
                            'last_run' => $matches[1],
                            'source' => 'log_file',
                        ];
                    }
                }
            } catch (Exception $e) {
                // Fallback if shell commands are disabled
            }
        }

        return [
            'last_run' => 'Unknown',
            'source' => 'not_found',
        ];
    }
}
