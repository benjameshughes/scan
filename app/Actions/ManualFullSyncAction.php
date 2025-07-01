<?php

namespace App\Actions;

use App\Services\LinnworksApiService;
use App\Models\SyncProgress;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

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
                'dry_run' => $dryRun
            ],
            'current_operation' => 'Initializing sync...',
            'started_at' => now()
        ]);
        
        $stats = [
            'total_processed' => 0,
            'created' => 0,
            'queued' => 0,
            'errors' => 0,
            'batches_processed' => 0,
            'execution_time' => 0,
            'dry_run' => $dryRun,
            'session_id' => $sessionId
        ];
        
        Log::info('Manual full sync started', ['dry_run' => $dryRun, 'session_id' => $sessionId]);
        
        try {
            $page = 1;
            $batchSize = 100;
            $hasMorePages = true;
            
            // First, update progress with initial operation
            $this->updateProgress('Connecting to Linnworks API...', $stats);
            
            while ($hasMorePages) {
                $this->updateProgress("Processing batch {$page} (fetching {$batchSize} products)...", $stats, [
                    'current_batch' => $page,
                    'batch_size' => $batchSize
                ]);
                
                Log::info("Processing batch {$page} (page size: {$batchSize})");
                
                // Get products from Linnworks
                $linnworksProducts = $this->linnworksService->getAllProducts($page, $batchSize);
                
                if (empty($linnworksProducts)) {
                    Log::info("No more products found on page {$page}, sync complete");
                    $this->updateProgress("No more products found. Sync completed.", $stats);
                    $hasMorePages = false;
                    break;
                }
                
                $this->updateProgress("Processing " . count($linnworksProducts) . " products from batch {$page}...", $stats, [
                    'current_batch' => $page,
                    'products_in_batch' => count($linnworksProducts)
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
                    'running_totals' => $stats
                ]);
                
                $this->updateProgress("Batch {$page} completed. Processed: {$batchStats['processed']}, Created: {$batchStats['created']}, Queued: {$batchStats['queued']}", $stats);
                
                // If we got fewer products than batch size, we're done
                if (count($linnworksProducts) < $batchSize) {
                    $hasMorePages = false;
                }
                
                $page++;
                
                // Add a small delay to avoid overwhelming the API
                if ($hasMorePages) {
                    $this->updateProgress("Waiting before next batch to avoid API rate limits...", $stats);
                    usleep(250000); // 250ms delay between batches
                }
            }
            
        } catch (Exception $e) {
            Log::error('Manual full sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'stats_so_far' => $stats
            ]);
            
            $stats['errors']++;
            $stats['error_message'] = $e->getMessage();
            
            // Update progress with error
            if ($this->progressTracker) {
                $this->progressTracker->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'stats' => $stats,
                    'completed_at' => now()
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
                'completed_at' => now()
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
                'current_batch' => $batchInfo
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
            // Get first page to estimate total
            $firstBatch = $this->linnworksService->getAllProducts(1, 100);
            $estimatedTotal = count($firstBatch) > 0 ? 'Unknown (API does not provide total count)' : 0;
            
            return [
                'estimated_total' => $estimatedTotal,
                'batch_size' => 100,
                'estimated_batches' => 'Unknown',
                'last_sync' => $this->getLastSyncInfo()
            ];
            
        } catch (Exception $e) {
            return [
                'error' => 'Could not connect to Linnworks API: ' . $e->getMessage(),
                'last_sync' => $this->getLastSyncInfo()
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
                $command = "tail -n 1000 " . escapeshellarg($logPath);
                $logs = shell_exec($command);
                
                if ($logs) {
                    // Look for recent sync log entries
                    if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*Daily Linnworks sync completed/', $logs, $matches)) {
                        return [
                            'last_run' => $matches[1],
                            'source' => 'log_file'
                        ];
                    }
                }
            } catch (Exception $e) {
                // Fallback if shell commands are disabled
            }
        }
        
        return [
            'last_run' => 'Unknown',
            'source' => 'not_found'
        ];
    }
}