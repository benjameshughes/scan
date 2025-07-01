<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Actions\DailyLinnworksSyncAction;
use App\Services\LinnworksApiService;
use Illuminate\Support\Facades\Cache;

class DailyLinnworksSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'linnworks:daily-sync 
                            {--dry-run : Show what would be synced without making changes}
                            {--batch-size=100 : Number of items to process per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily sync of all Linnworks products - auto-create new, queue updates for approval';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting daily Linnworks sync...');
        
        $stats = [
            'total_processed' => 0,
            'new_created' => 0,
            'updates_queued' => 0,
            'errors' => 0
        ];
        
        try {
            $linnworksService = app(LinnworksApiService::class);
            $syncAction = app(DailyLinnworksSyncAction::class);
            
            // Get all products from Linnworks in batches
            $page = 1;
            $batchSize = (int) $this->option('batch-size');
            $isDryRun = $this->option('dry-run');
            
            if ($isDryRun) {
                $this->warn('Running in DRY RUN mode - no changes will be made');
            }
            
            do {
                $this->line("Processing batch {$page} (items " . (($page-1) * $batchSize + 1) . "-" . ($page * $batchSize) . ")");
                
                $linnworksProducts = $linnworksService->getAllProducts($page, $batchSize);
                
                if (empty($linnworksProducts)) {
                    break;
                }
                
                $batchStats = $syncAction->processBatch($linnworksProducts, $isDryRun);
                
                $stats['total_processed'] += $batchStats['processed'];
                $stats['new_created'] += $batchStats['created'];
                $stats['updates_queued'] += $batchStats['queued'];
                $stats['errors'] += $batchStats['errors'];
                
                $page++;
                
                // Progress indicator
                $this->line("  ✓ Processed: {$batchStats['processed']} | Created: {$batchStats['created']} | Queued: {$batchStats['queued']}");
                
                // Small delay to avoid overwhelming the API
                if (!$isDryRun && count($linnworksProducts) === $batchSize) {
                    sleep(1);
                }
                
            } while (count($linnworksProducts) === $batchSize);
            
            // Store last sync time
            if (!$isDryRun) {
                Cache::put('last_linnworks_sync', now(), now()->addDays(7));
            }
            
            $this->displaySummary($stats);
            
        } catch (\Exception $e) {
            $this->error("Sync failed: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Display the sync summary
     */
    private function displaySummary(array $stats): void
    {
        $this->info("\n📊 Daily Sync Summary:");
        $this->table(['Metric', 'Count'], [
            ['Total Processed', number_format($stats['total_processed'])],
            ['New Products Created', number_format($stats['new_created'])],
            ['Updates Queued for Review', number_format($stats['updates_queued'])],
            ['Errors', number_format($stats['errors'])]
        ]);
        
        if ($stats['errors'] > 0) {
            $this->warn("⚠️  {$stats['errors']} errors occurred during sync. Check logs for details.");
        }
        
        if ($stats['updates_queued'] > 0) {
            $this->info("📝 {$stats['updates_queued']} product updates are waiting for manual review.");
        }
    }
}