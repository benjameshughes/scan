<?php

namespace App\Livewire;

use App\Models\Location;
use App\Services\LinnworksApiService;
use Livewire\Component;

class LocationsDashboard extends Component
{
    public function render()
    {
        // Get location statistics
        $totalLocations = Location::count();
        $activeLocations = Location::where('is_active', true)->count();
        $recentlyUsed = Location::whereNotNull('last_used_at')
            ->where('last_used_at', '>=', now()->subDays(30))
            ->count();
        $neverUsed = Location::whereNull('last_used_at')->count();

        // Get top locations by usage
        $topLocationsByUsage = Location::where('use_count', '>', 0)
            ->orderBy('use_count', 'desc')
            ->limit(10)
            ->get();

        // Get recently used locations
        $recentlyUsedLocations = Location::whereNotNull('last_used_at')
            ->orderBy('last_used_at', 'desc')
            ->limit(10)
            ->get();

        // Get location usage trends (last 7 days)
        $usageTrends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayUsage = Location::whereDate('last_used_at', $date)->count();
            $usageTrends[] = [
                'date' => $date->format('M j'),
                'count' => $dayUsage,
            ];
        }

        // Get locations that need attention (inactive or never used)
        $locationsNeedingAttention = Location::where(function($query) {
            $query->where('is_active', false)
                  ->orWhereNull('last_used_at');
        })->limit(5)->get();

        return view('livewire.locations-dashboard', [
            'stats' => [
                'total' => $totalLocations,
                'active' => $activeLocations,
                'recently_used' => $recentlyUsed,
                'never_used' => $neverUsed,
            ],
            'topLocationsByUsage' => $topLocationsByUsage,
            'recentlyUsedLocations' => $recentlyUsedLocations,
            'usageTrends' => $usageTrends,
            'locationsNeedingAttention' => $locationsNeedingAttention,
        ]);
    }
}
