<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingProductUpdate extends Model
{
    protected $fillable = [
        'product_id',
        'linnworks_data',
        'changes_detected',
        'status',
        'reviewed_by',
        'reviewed_at',
        'notes'
    ];
    
    protected $casts = [
        'linnworks_data' => 'json',
        'changes_detected' => 'json',
        'reviewed_at' => 'datetime'
    ];
    
    /**
     * Get the product that this update is for
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Get the user who reviewed this update
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
    
    /**
     * Scope a query to only include pending updates
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    /**
     * Check if this update has been reviewed
     */
    public function isReviewed(): bool
    {
        return in_array($this->status, ['approved', 'rejected']);
    }
}
