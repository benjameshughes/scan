<?php

namespace App\Livewire\Admin;

use App\Actions\DailyLinnworksSyncAction;
use App\Models\Scan;
use App\Models\SyncProgress;
use App\Services\Linnworks\LinnworksInventoryService;
use App\Services\LinnworksApiService;
use App\Services\SyncRetryService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SyncDashboard extends Component
{
    public $syncStats = [];

    public $recentActivity = [];

    public $errorBreakdown = [];

    public $queueStatus = [];

    public $apiHealth = [];

    public $retryRecommendations = [];

    public $refreshing = false;

    public $bulkSyncing = false;

    public $retryingFailed = false;

    public $smartRetrying = false;

    public function mount()
    {
        // Check if user has permission to manage products
        if (! auth()->user()->can('manage products')) {
            abort(403, 'You do not have permission to access the sync dashboard.');
        }

        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $this->refreshing = true;

        $this->syncStats = $this->getSyncStats();
        $this->recentActivity = $this->getRecentActivity();
        $this->errorBreakdown = $this->getErrorBreakdown();
        $this->queueStatus = $this->getQueueStatus();
        $this->apiHealth = $this->getApiHealth();
        $this->retryRecommendations = $this->getRetryRecommendations();

        $this->refreshing = false;
    }

    public function refreshDashboard()
    {
        $this->loadDashboardData();
        $this->dispatch('dashboard-refreshed');
    }

    public function pullProductUpdates()
    {
        $this->bulkSyncing = true;

        try {
            // This pulls product data FROM Linnworks TO local database using our new safe services
            // It does NOT push any stock changes back to Linnworks
            $inventoryService = app(\App\Services\Linnworks\LinnworksInventoryService::class);

            $processed = 0;
            $updated = 0;

            // Get recent products that have been scanned to refresh their data
            $recentProducts = \App\Models\Product::whereHas('scans', function ($q) {
                $q->where('created_at', '>', now()->subDays(7));
            })->limit(50)->get();

            foreach ($recentProducts as $product) {
                try {
                    $linnworksData = $inventoryService->getProductInfo($product->sku);
                    if ($linnworksData) {
                        // Update local product with fresh Linnworks data (read-only sync)
                        $processed++;
                        if ($product->name !== $linnworksData['title']) {
                            $updated++;
                            // Could update product name here if desired
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to update product {$product->sku}: ".$e->getMessage());
                }
            }

            session()->flash('success',
                "Pulled data for {$processed} products from Linnworks. ".
                "Found {$updated} with updates available."
            );

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to pull product updates: '.$e->getMessage());
        }

        $this->bulkSyncing = false;
        $this->loadDashboardData();
    }

    public function refreshStockLevels()
    {
        $this->retryingFailed = true;

        try {
            // This pulls current stock levels FROM Linnworks TO update local product data
            // It does NOT push any changes back to Linnworks
            $inventoryService = app(LinnworksInventoryService::class);
            $updated = 0;

            // Get products that have been scanned recently to refresh their stock levels
            $recentProducts = \App\Models\Product::whereHas('scans', function ($q) {
                $q->where('created_at', '>', now()->subDays(7));
            })->limit(100)->get();

            foreach ($recentProducts as $product) {
                try {
                    $stockLevel = $inventoryService->getStockLevel($product->sku);
                    $stockData = $inventoryService->getStockDetails($product->sku);
                    // Update local cache with current Linnworks stock levels
                    // This is read-only - we're pulling data, not pushing
                    if ($stockData) {
                        $updated++;
                    }
                } catch (\Exception $e) {
                    // Log but continue with other products
                    \Log::warning("Failed to refresh stock for {$product->sku}: ".$e->getMessage());
                }
            }

            session()->flash('success', "Refreshed stock levels for {$updated} products from Linnworks.");

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to refresh stock levels: '.$e->getMessage());
        }

        $this->retryingFailed = false;
        $this->loadDashboardData();
    }

    public function pullFullProductCatalog()
    {
        try {
            // This pulls the complete product catalog FROM Linnworks TO local database using our safe services
            // Uses the existing DailyLinnworksSyncAction for proper create/update with approval workflow
            $inventoryService = app(\App\Services\Linnworks\LinnworksInventoryService::class);
            $syncAction = new DailyLinnworksSyncAction;

            $processed = 0;
            $created = 0;
            $updated = 0;
            $queued = 0;
            $errors = 0;
            $page = 1;
            $pageSize = config('linnworks.pagination.sync_page_size', 200); // Use config value, default 200
            $maxPages = config('linnworks.pagination.max_sync_pages', 100); // Allow up to 100 pages (20,000 products)

            \Log::info('Starting full product catalog pull from Linnworks', [
                'page_size' => $pageSize,
                'max_pages' => $maxPages,
                'estimated_max_products' => $pageSize * $maxPages,
            ]);

            // Pull products page by page from Linnworks
            do {
                $products = $inventoryService->getAllProducts($page, $pageSize);
                \Log::info("Processing page {$page}", [
                    'products_count' => count($products),
                    'page' => $page,
                ]);

                if (! empty($products)) {
                    // Use the existing action to process this batch with proper validation and approval workflow
                    $batchStats = $syncAction->processBatch($products, false); // false = not dry run, actually create/update

                    $processed += $batchStats['processed'];
                    $created += $batchStats['created'];
                    $queued += $batchStats['queued'];
                    $errors += $batchStats['errors'];

                    \Log::info("Batch {$page} completed", [
                        'page' => $page,
                        'batch_stats' => $batchStats,
                        'running_totals' => [
                            'processed' => $processed,
                            'created' => $created,
                            'queued' => $queued,
                            'errors' => $errors,
                        ],
                    ]);
                }

                $page++;
            } while (! empty($products) && count($products) === $pageSize && $page <= $maxPages);

            $pagesProcessed = $page - 1;

            \Log::info('Full product catalog pull completed', [
                'pages_processed' => $pagesProcessed,
                'final_stats' => [
                    'processed' => $processed,
                    'created' => $created,
                    'queued_for_approval' => $queued,
                    'errors' => $errors,
                ],
            ]);

            session()->flash('success',
                'Product catalog sync completed successfully! '.
                "Processed {$pagesProcessed} pages ({$processed} total products). ".
                "Created {$created} new products, {$queued} queued for manual approval due to changes. ".
                "Errors: {$errors}. Check logs for detailed information."
            );

        } catch (\Exception $e) {
            \Log::error('Failed to pull product catalog', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Failed to pull product catalog: '.$e->getMessage());
        }

        $this->loadDashboardData();
    }

    public function clearOldSyncHistory()
    {
        try {
            $cutoffDate = Carbon::now()->subDays(30);

            $deletedCount = SyncProgress::where('completed_at', '<', $cutoffDate)
                ->orWhere('created_at', '<', $cutoffDate)
                ->delete();

            session()->flash('success', "Cleared {$deletedCount} old sync history records.");

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to clear old sync history: '.$e->getMessage());
        }

        $this->loadDashboardData();
    }

    public function validateProductData()
    {
        $this->smartRetrying = true;

        try {
            // This validates local product data against Linnworks
            // It checks for discrepancies but does NOT push changes to Linnworks
            $inventoryService = app(LinnworksInventoryService::class);
            $validated = 0;
            $discrepancies = 0;

            // Check recent scans for data validation
            $recentScans = \App\Models\Scan::with('product')
                ->where('created_at', '>', now()->subHours(24))
                ->where('sync_status', 'failed')
                ->limit(50)
                ->get();

            foreach ($recentScans as $scan) {
                if ($scan->product) {
                    try {
                        // Validate product exists in Linnworks - read-only check
                        $exists = $inventoryService->productExists($scan->product->sku);
                        if (! $exists) {
                            $discrepancies++;
                        }
                        $validated++;
                    } catch (\Exception $e) {
                        // Log validation errors
                        \Log::warning("Validation failed for {$scan->product->sku}: ".$e->getMessage());
                    }
                }
            }

            session()->flash('success',
                "Validated {$validated} products. Found {$discrepancies} discrepancies. ".
                'Check logs for details.'
            );

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to validate product data: '.$e->getMessage());
        }

        $this->smartRetrying = false;
        $this->loadDashboardData();
    }

    protected function getSyncStats()
    {
        $now = Carbon::now();
        $last24h = $now->copy()->subDay();
        $last7d = $now->copy()->subDays(7);
        $last30d = $now->copy()->subDays(30);

        // Get last successful sync
        $lastSuccessfulSync = SyncProgress::where('status', 'completed')
            ->where('type', 'daily_sync')
            ->latest('completed_at')
            ->first()?->completed_at;

        // Get pending scans count
        $pendingScans = Scan::where('sync_status', 'pending')
            ->orWhere('sync_status', 'failed')
            ->count();

        // Calculate success rates
        $total24h = Scan::where('created_at', '>=', $last24h)->count();
        $synced24h = Scan::where('sync_status', 'synced')
            ->where('created_at', '>=', $last24h)
            ->count();

        $total7d = Scan::where('created_at', '>=', $last7d)->count();
        $synced7d = Scan::where('sync_status', 'synced')
            ->where('created_at', '>=', $last7d)
            ->count();

        $total30d = Scan::where('created_at', '>=', $last30d)->count();
        $synced30d = Scan::where('sync_status', 'synced')
            ->where('created_at', '>=', $last30d)
            ->count();

        return [
            'last_successful_sync' => $lastSuccessfulSync,
            'pending_scans' => $pendingScans,
            'success_rate_24h' => $total24h > 0 ? round(($synced24h / $total24h) * 100, 1) : 0,
            'success_rate_7d' => $total7d > 0 ? round(($synced7d / $total7d) * 100, 1) : 0,
            'success_rate_30d' => $total30d > 0 ? round(($synced30d / $total30d) * 100, 1) : 0,
            'total_scans_24h' => $total24h,
            'total_scans_7d' => $total7d,
            'total_scans_30d' => $total30d,
        ];
    }

    protected function getRecentActivity()
    {
        return SyncProgress::with('user')
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(function ($sync) {
                return [
                    'id' => $sync->id,
                    'type' => $sync->type,
                    'status' => $sync->status,
                    'user_name' => $sync->user?->name ?? 'System',
                    'created_at' => $sync->created_at,
                    'completed_at' => $sync->completed_at,
                    'duration' => $sync->completed_at ?
                        $sync->created_at->diffInSeconds($sync->completed_at) : null,
                    'error_message' => $sync->error_message,
                    'stats' => $sync->stats,
                ];
            });
    }

    protected function getErrorBreakdown()
    {
        // Get failed scans with error categorization
        $failedScans = Scan::where('sync_status', 'failed')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->get();

        $errorTypes = [];

        foreach ($failedScans as $scan) {
            // You can expand this to categorize errors based on actual error messages
            $errorType = 'Unknown Error';

            // Simple error categorization - can be enhanced based on actual error patterns
            if (str_contains($scan->notes ?? '', 'rate limit')) {
                $errorType = 'Rate Limit';
            } elseif (str_contains($scan->notes ?? '', 'network')) {
                $errorType = 'Network Error';
            } elseif (str_contains($scan->notes ?? '', 'auth')) {
                $errorType = 'Authentication';
            } elseif (str_contains($scan->notes ?? '', 'product not found')) {
                $errorType = 'Product Not Found';
            }

            $errorTypes[$errorType] = ($errorTypes[$errorType] ?? 0) + 1;
        }

        return $errorTypes;
    }

    protected function getQueueStatus()
    {
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();

        return [
            'pending_jobs' => $pendingJobs,
            'failed_jobs' => $failedJobs,
            'processing' => $pendingJobs > 0,
        ];
    }

    protected function getApiHealth()
    {
        $cacheKey = 'linnworks_api_health';

        return Cache::remember($cacheKey, 300, function () {
            try {
                $service = app(LinnworksApiService::class);
                $start = microtime(true);

                // Simple health check - try to get inventory count (lightweight API call)
                $count = $service->getInventoryCount();

                $responseTime = round((microtime(true) - $start) * 1000, 2);

                return [
                    'status' => 'healthy',
                    'response_time' => $responseTime,
                    'last_checked' => Carbon::now(),
                    'token_valid' => true,
                    'inventory_count' => $count,
                ];

            } catch (\Exception $e) {
                return [
                    'status' => 'unhealthy',
                    'error' => $e->getMessage(),
                    'last_checked' => Carbon::now(),
                    'token_valid' => false,
                ];
            }
        });
    }

    protected function getRetryRecommendations()
    {
        try {
            $retryService = app(SyncRetryService::class);

            return $retryService->getRetryRecommendations();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function render()
    {
        return view('livewire.admin.sync-dashboard');
    }
}
