<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'moved_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Movement types
     */
    const TYPE_BAY_REFILL = 'bay_refill';
    const TYPE_MANUAL_TRANSFER = 'manual_transfer';
    const TYPE_SCAN_ADJUSTMENT = 'scan_adjustment';

    /**
     * Get the product for this movement
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who performed the movement
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the from location if it exists
     */
    public function fromLocation()
    {
        return $this->belongsTo(Location::class, 'from_location_id', 'location_id');
    }

    /**
     * Get the to location if it exists
     */
    public function toLocation()
    {
        return $this->belongsTo(Location::class, 'to_location_id', 'location_id');
    }

    /**
     * Get the related scan if this movement was from a scan
     */
    public function scan()
    {
        return $this->morphTo('reference');
    }

    /**
     * Create a bay refill movement record
     */
    public static function createBayRefill($product, $fromLocationId, $fromLocationCode, $quantity, $userId, $metadata = [])
    {
        return static::create([
            'product_id' => $product->id,
            'from_location_id' => $fromLocationId,
            'from_location_code' => $fromLocationCode,
            'to_location_id' => 'default', // Bay refills always go to default location
            'to_location_code' => 'Default',
            'quantity' => $quantity,
            'type' => self::TYPE_BAY_REFILL,
            'user_id' => $userId,
            'moved_at' => now(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get formatted movement type
     */
    public function getFormattedTypeAttribute()
    {
        return match ($this->type) {
            self::TYPE_BAY_REFILL => 'Bay Refill',
            self::TYPE_MANUAL_TRANSFER => 'Manual Transfer',
            self::TYPE_SCAN_ADJUSTMENT => 'Scan Adjustment',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    /**
     * Get movement direction display
     */
    public function getMovementDisplayAttribute()
    {
        $from = $this->from_location_code ?: 'Unknown';
        $to = $this->to_location_code ?: 'Unknown';
        
        return "{$from} → {$to}";
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('moved_at', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering by location
     */
    public function scopeForLocation($query, $locationId)
    {
        return $query->where('from_location_id', $locationId)
                     ->orWhere('to_location_id', $locationId);
    }
}