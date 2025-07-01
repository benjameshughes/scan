<?php

namespace App\Actions;

use App\Services\LinnworksApiService;
use Illuminate\Support\Facades\Log;
use Exception;

class ManualFullSyncAction
{
    protected LinnworksApiService $linnworksService;
    protected DailyLinnworksSyncAction $syncAction;
    
    public function __construct(LinnworksApiService $linnworksService, DailyLinnworksSyncAction $syncAction)
    {
        $this->linnworksService = $linnworksService;
        $this->syncAction = $syncAction;
    }
    
    /**
     * Execute a manual full sync with progress tracking
     */
    public function execute(bool $dryRun = false): array
    {
        $startTime = microtime(true);
        $stats = [
            'total_processed' => 0,
            'created' => 0,
            'queued' => 0,
            'errors' => 0,
            'batches_processed' => 0,
            'execution_time' => 0,
            'dry_run' => $dryRun
        ];
        
        Log::info('Manual full sync started', ['dry_run' => $dryRun]);
        
        try {
            $page = 1;
            $batchSize = 100;
            $hasMorePages = true;
            
            while ($hasMorePages) {
                Log::info("Processing batch {$page} (page size: {$batchSize})");
                
                // Get products from Linnworks
                $linnworksProducts = $this->linnworksService->getAllProducts($page, $batchSize);
                
                if (empty($linnworksProducts)) {
                    Log::info("No more products found on page {$page}, sync complete");
                    $hasMorePages = false;
                    break;
                }
                
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
                
                // If we got fewer products than batch size, we're done
                if (count($linnworksProducts) < $batchSize) {
                    $hasMorePages = false;
                }
                
                $page++;
                
                // Add a small delay to avoid overwhelming the API
                usleep(250000); // 250ms delay between batches
            }
            
        } catch (Exception $e) {
            Log::error('Manual full sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'stats_so_far' => $stats
            ]);
            
            $stats['errors']++;
            $stats['error_message'] = $e->getMessage();
        }
        
        $stats['execution_time'] = round(microtime(true) - $startTime, 2);
        
        Log::info('Manual full sync completed', $stats);
        
        return $stats;
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