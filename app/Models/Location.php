<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'location_id',
        'code',
        'name',
        'use_count',
        'last_used_at',
        'is_active',
        'qr_code',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
        'use_count' => 'integer',
    ];

    /**
     * Get locations ordered by frecency (frequency + recency)
     * More recent and more frequently used locations appear first
     * Pure Eloquent implementation
     */
    public function scopeFrecencyOrder(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->orderBy('use_count', 'desc')
            ->orderBy('last_used_at', 'desc')
            ->orderBy('code');
    }

    /**
     * Search locations by code or name
     */
    public function scopeLocationSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('code', 'LIKE', "%{$search}%")
                ->orWhere('name', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Record usage of this location (increment use_count and update last_used_at)
     */
    public function recordUsage(): void
    {
        $this->increment('use_count');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Get display name (prefers name, falls back to code)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->code;
    }

    /**
     * Create or update location from Linnworks data
     */
    public static function createOrUpdateFromLinnworks(string $locationId, string $locationName): self
    {
        return self::updateOrCreate(
            ['location_id' => $locationId],
            [
                'name' => $locationName,
                'code' => $locationName, // Use name as code initially
                'is_active' => true,
            ]
        );
    }
}
