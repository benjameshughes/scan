<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Actions\ManualFullSyncAction;
use App\Models\SyncProgress;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ManualSync extends Component
{
    public $isRunning = false;
    public $showResults = false;
    public $syncStats = null;
    public $estimatedInfo = null;
    public $dryRun = false;
    public $currentProgress = null;
    public $sessionId = null;
    
    protected ManualFullSyncAction $syncAction;
    
    public function boot(ManualFullSyncAction $syncAction)
    {
        $this->syncAction = $syncAction;
    }
    
    public function mount()
    {
        $this->loadEstimatedInfo();
    }
    
    /**
     * Load estimated sync information
     */
    public function loadEstimatedInfo()
    {
        try {
            $this->estimatedInfo = $this->syncAction->getEstimatedInfo();
        } catch (\Exception $e) {
            $this->estimatedInfo = [
                'error' => 'Failed to load sync information: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Execute manual sync
     */
    public function executeSync()
    {
        // Verify admin permission
        if (!auth()->user()->can('manage products')) {
            session()->flash('error', 'You do not have permission to run manual syncs.');
            return;
        }
        
        $this->isRunning = true;
        $this->showResults = false;
        $this->syncStats = null;
        $this->currentProgress = null;
        $this->sessionId = Str::uuid()->toString();
        
        // Dispatch event to start polling
        $this->dispatch('sync-started');
        
        try {
            Log::info('Manual sync initiated by user', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
                'dry_run' => $this->dryRun,
                'session_id' => $this->sessionId
            ]);
            
            // Execute the sync with session tracking
            $this->syncStats = $this->syncAction->execute($this->dryRun, $this->sessionId);
            
            $this->showResults = true;
            
            // Flash appropriate message
            if ($this->dryRun) {
                session()->flash('message', 'Dry run completed successfully. No changes were made.');
            } else {
                $message = "Sync completed! Processed {$this->syncStats['total_processed']} products. ";
                $message .= "Created: {$this->syncStats['created']}, Queued: {$this->syncStats['queued']}, Errors: {$this->syncStats['errors']}";
                session()->flash('message', $message);
            }
            
        } catch (\Exception $e) {
            Log::error('Manual sync failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'session_id' => $this->sessionId
            ]);
            
            session()->flash('error', 'Sync failed: ' . $e->getMessage());
            
        } finally {
            $this->isRunning = false;
            
            // Refresh estimated info after sync
            $this->loadEstimatedInfo();
        }
    }
    
    /**
     * Get current progress for the running sync
     */
    public function getProgress()
    {
        if (!$this->sessionId) {
            return null;
        }
        
        $progress = $this->syncAction->getProgress($this->sessionId);
        
        if ($progress) {
            $this->currentProgress = [
                'operation' => $progress->current_operation,
                'stats' => $progress->stats,
                'batch_info' => $progress->current_batch,
                'status' => $progress->status,
                'is_running' => $progress->isRunning(),
                'progress_percentage' => $progress->getProgressPercentage()
            ];
            
            // If sync completed, get final results
            if ($progress->isCompleted() && $this->isRunning) {
                $this->syncStats = $progress->stats;
                $this->showResults = true;
                $this->isRunning = false;
            } elseif ($progress->isFailed() && $this->isRunning) {
                $this->isRunning = false;
                session()->flash('error', 'Sync failed: ' . $progress->error_message);
            }
        }
        
        return $this->currentProgress;
    }
    
    /**
     * Clear results
     */
    public function clearResults()
    {
        $this->showResults = false;
        $this->syncStats = null;
    }
    
    /**
     * Toggle dry run mode
     */
    public function toggleDryRun()
    {
        $this->dryRun = !$this->dryRun;
    }
    
    public function render()
    {
        return view('livewire.admin.manual-sync');
    }
}