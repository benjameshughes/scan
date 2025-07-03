<?php

namespace App\Livewire\Components;

use Illuminate\Support\Collection;
use Livewire\Component;

class SmartLocationSelector extends Component
{
    public ?string $selectedLocationId = null;

    public string $searchTerm = '';

    public array $locations = [];

    public bool $showDropdown = false;

    public string $placeholder = 'Select a location...';

    // Component configuration
    public bool $showSearch = true;

    public bool $showFavorites = true;

    public bool $showRecent = true;

    public int $maxRecentLocations = 3;

    protected $listeners = [
        'locationSelected' => 'selectLocation',
        'locationsUpdated' => 'updateLocations',
    ];

    public function mount(array $locations = [], ?string $selectedLocationId = null)
    {
        $this->locations = $locations;
        $this->selectedLocationId = $selectedLocationId;
    }

    public function updatedSearchTerm()
    {
        $this->showDropdown = ! empty($this->searchTerm) || $this->showDropdown;
    }

    public function selectLocation($locationId)
    {
        $this->selectedLocationId = $locationId;
        $this->showDropdown = false;
        $this->searchTerm = '';

        // Emit event to parent component
        $this->dispatch('locationChanged', $locationId);

        // Track usage for smart suggestions
        $this->trackLocationUsage($locationId);
    }

    public function toggleDropdown()
    {
        $this->showDropdown = ! $this->showDropdown;
        if ($this->showDropdown) {
            $this->dispatch('focus-search');
        }
    }

    public function closeDropdown()
    {
        $this->showDropdown = false;
        $this->searchTerm = '';
    }

    public function getFilteredLocationsProperty(): Collection
    {
        $locations = collect($this->locations);

        if (empty($this->searchTerm)) {
            return $this->getSortedLocations($locations);
        }

        return $locations->filter(function ($location) {
            return str_contains(
                strtolower($location['LocationName']),
                strtolower($this->searchTerm)
            );
        });
    }

    public function getSelectedLocationProperty(): ?array
    {
        if (! $this->selectedLocationId) {
            return null;
        }

        return collect($this->locations)->firstWhere('StockLocationId', $this->selectedLocationId);
    }

    protected function getSortedLocations(Collection $locations): Collection
    {
        $recentLocations = $this->getRecentLocations();
        $favoriteLocations = $this->getFavoriteLocations();

        return $locations->sortBy(function ($location) use ($recentLocations, $favoriteLocations) {
            $locationId = $location['StockLocationId'];

            // Favorites first
            if (in_array($locationId, $favoriteLocations)) {
                return 0;
            }

            // Recent locations second
            if (in_array($locationId, $recentLocations)) {
                return 1;
            }

            // Alphabetical for others
            return 2;
        })->values();
    }

    public function getRecentLocations(): array
    {
        $userId = auth()->id();
        $cacheKey = "recent_locations_user_{$userId}";

        return cache()->get($cacheKey, []);
    }

    public function getFavoriteLocations(): array
    {
        $userId = auth()->id();
        $settings = auth()->user()->settings ?? [];

        return $settings['favorite_locations'] ?? [];
    }

    protected function trackLocationUsage(string $locationId): void
    {
        $userId = auth()->id();
        $cacheKey = "recent_locations_user_{$userId}";

        $recentLocations = cache()->get($cacheKey, []);

        // Remove if already exists
        $recentLocations = array_filter($recentLocations, fn ($id) => $id !== $locationId);

        // Add to front
        array_unshift($recentLocations, $locationId);

        // Limit to max recent locations
        $recentLocations = array_slice($recentLocations, 0, $this->maxRecentLocations);

        // Cache for 30 days
        cache()->put($cacheKey, $recentLocations, now()->addDays(30));
    }

    public function toggleFavorite(string $locationId)
    {
        $user = auth()->user();
        $settings = $user->settings ?? [];
        $favorites = $settings['favorite_locations'] ?? [];

        if (in_array($locationId, $favorites)) {
            $favorites = array_filter($favorites, fn ($id) => $id !== $locationId);
        } else {
            $favorites[] = $locationId;
        }

        $settings['favorite_locations'] = array_values($favorites);
        $user->update(['settings' => $settings]);

        $this->dispatch('favoriteToggled', $locationId);
    }

    public function render()
    {
        return view('livewire.components.smart-location-selector');
    }
}
