<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scan extends Model
{
    /** @use HasFactory<\Database\Factories\ScanFactory> */
    use HasFactory;

    protected $guarded = [
        'id',
        'created_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'last_sync_attempt' => 'datetime',
        'synced_at' => 'datetime',
        'sync_metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        // Standard belongsTo relationship for primary barcode
        return $this->belongsTo(Product::class, 'barcode', 'barcode');
    }

    /**
     * Get the product by checking all barcode fields
     */
    public function getProductAttribute()
    {
        // Check if we already have a cached product
        if (isset($this->relations['product']) && $this->relations['product']) {
            return $this->relations['product'];
        }

        // Search for product by checking all barcode fields
        $product = Product::where('barcode', $this->barcode)
            ->orWhere('barcode_2', $this->barcode)
            ->orWhere('barcode_3', $this->barcode)
            ->first();

        // Cache the result
        $this->setRelation('product', $product);

        return $product;
    }

    /**
     * Get the formatted date for humans.
     * If the year is not this year, return the date in the format of "MMM d, YYYY".
     */
    public function dateForHumans()
    {
        return $this->created_at->format(
            $this->created_at->year === now()->year ? 'MMM d, h:mm a' : 'MMM d, YYYY'
        );
    }
    
    /**
     * Get a human-readable error type
     */
    public function getErrorTypeDisplayAttribute()
    {
        return match($this->sync_error_type) {
            'network' => 'Network Error',
            'auth' => 'Authentication Error',
            'rate_limit' => 'Rate Limit Exceeded',
            'product_not_found' => 'Product Not Found',
            'api_error' => 'API Error',
            'timeout' => 'Request Timeout',
            'validation' => 'Validation Error',
            default => ucfirst(str_replace('_', ' ', $this->sync_error_type ?? 'Unknown Error'))
        };
    }
    
    /**
     * Get a user-friendly sync status with additional context
     */
    public function getSyncStatusDisplayAttribute()
    {
        return match($this->sync_status) {
            'pending' => 'Pending Sync',
            'synced' => 'Successfully Synced',
            'failed' => 'Sync Failed',
            default => ucfirst($this->sync_status ?? 'Unknown')
        };
    }
    
    /**
     * Check if this scan has failed multiple times
     */
    public function hasMultipleFailures()
    {
        return $this->sync_attempts > 1 && $this->sync_status === 'failed';
    }
    
    /**
     * Get the next retry attempt number
     */
    public function getNextRetryAttempt()
    {
        return $this->sync_attempts + 1;
    }
    
    /**
     * Record a sync attempt with error information
     */
    public function recordSyncAttempt($status, $errorMessage = null, $errorType = null, $metadata = [])
    {
        $this->update([
            'sync_status' => $status,
            'sync_attempts' => $this->sync_attempts + 1,
            'last_sync_attempt' => now(),
            'sync_error_message' => $errorMessage,
            'sync_error_type' => $errorType,
            'sync_metadata' => array_merge($this->sync_metadata ?? [], $metadata),
            'synced_at' => $status === 'synced' ? now() : null,
        ]);
    }
}
