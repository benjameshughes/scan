<?php

namespace App\Livewire\Admin;

use App\Models\PendingProductUpdate;
use App\Models\Scan;
use App\Models\SyncProgress;
use App\Actions\SyncAllPendingScans;
use App\Actions\DailyLinnworksSyncAction;
use App\Services\LinnworksApiService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SyncDashboard extends Component
{
    public $syncStats = [];
    public $recentActivity = [];
    public $errorBreakdown = [];
    public $queueStatus = [];
    public $apiHealth = [];
    
    public $refreshing = false;
    public $bulkSyncing = false;
    public $retryingFailed = false;
    
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
        
        $this->refreshing = false;
    }
    
    public function refreshDashboard()
    {
        $this->loadDashboardData();
        $this->dispatch('dashboard-refreshed');
    }
    
    public function syncAllPending()
    {
        $this->bulkSyncing = true;
        
        try {
            $pendingScans = Scan::where('sync_status', 'pending')
                ->orWhere('sync_status', 'failed')
                ->count();
            
            if ($pendingScans > 0) {
                $action = new SyncAllPendingScans();
                $action->execute();
                
                session()->flash('success', "Queued {$pendingScans} scans for sync processing.");
            } else {
                session()->flash('info', 'No pending scans to sync.');
            }
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to queue scans: ' . $e->getMessage());
        }
        
        $this->bulkSyncing = false;
        $this->loadDashboardData();
    }
    
    public function retryAllFailed()
    {
        $this->retryingFailed = true;
        
        try {
            $failedScans = Scan::where('sync_status', 'failed')->count();
            
            if ($failedScans > 0) {
                // Reset failed scans to pending for retry
                Scan::where('sync_status', 'failed')
                    ->update(['sync_status' => 'pending']);
                
                $action = new SyncAllPendingScans();
                $action->execute();
                
                session()->flash('success', "Retrying {$failedScans} failed scans.");
            } else {
                session()->flash('info', 'No failed scans to retry.');
            }
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to retry scans: ' . $e->getMessage());
        }
        
        $this->retryingFailed = false;
        $this->loadDashboardData();
    }
    
    public function runFullSync()
    {
        try {
            $action = new DailyLinnworksSyncAction();
            $action->execute();
            
            session()->flash('success', 'Full sync has been queued for processing.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to start full sync: ' . $e->getMessage());
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
            session()->flash('error', 'Failed to clear old sync history: ' . $e->getMessage());
        }
        
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
                
                // Simple health check - try to get token
                $token = $service->getToken();
                
                $responseTime = round((microtime(true) - $start) * 1000, 2);
                
                return [
                    'status' => 'healthy',
                    'response_time' => $responseTime,
                    'last_checked' => Carbon::now(),
                    'token_valid' => !empty($token),
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
    
    public function render()
    {
        return view('livewire.admin.sync-dashboard')
            ->layout('layouts.app')
            ->title('Sync Dashboard');
    }
}