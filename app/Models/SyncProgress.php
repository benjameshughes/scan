<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncProgress extends Model
{
    protected $table = 'sync_progress';
    
    protected $fillable = [
        'session_id',
        'user_id',
        'type',
        'status',
        'stats',
        'current_batch',
        'current_operation',
        'error_message',
        'started_at',
        'completed_at'
    ];
    
    protected $casts = [
        'stats' => 'json',
        'current_batch' => 'json',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];
    
    /**
     * Get the user who initiated this sync
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Check if the sync is still running
     */
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }
    
    /**
     * Check if the sync completed successfully
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
    
    /**
     * Check if the sync failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
    
    /**
     * Get the progress percentage (if available)
     */
    public function getProgressPercentage(): ?int
    {
        $stats = $this->stats;
        $batch = $this->current_batch;
        
        if (!$stats || !$batch) {
            return null;
        }
        
        $totalProducts = $batch['estimated_total_products'] ?? null;
        $processedProducts = $stats['total_processed'] ?? 0;
        
        // If we have total products, calculate based on products processed
        if ($totalProducts && $totalProducts > 0) {
            return min(100, round(($processedProducts / $totalProducts) * 100));
        }
        
        // Fallback to batch-based calculation
        $totalBatches = $batch['estimated_total_batches'] ?? null;
        $currentBatch = $batch['current_batch'] ?? 0;
        
        if (!$totalBatches || $totalBatches === 0) {
            return null;
        }
        
        return min(100, round(($currentBatch / $totalBatches) * 100));
    }
}