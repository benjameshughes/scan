<?php

namespace App\Livewire;

use App\Models\Location;
use App\Services\LinnworksApiService;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class SmartLocationSelector extends Component
{
    public string $search = '';

    public string $selectedLocationId = '';

    public string $placeholder = 'Select location...';

    public bool $showDropdown = false;

    public bool $required = false;

    public string $label = 'Location';

    public string $errorMessage = '';

    public string $type = 'from';

    // Component properties
    public Collection $smartSuggestions;

    public Collection $searchResults;

    public bool $showCreateOption = false;

    public function mount()
    {
        $this->smartSuggestions = collect();
        $this->searchResults = collect();
        $this->loadSmartSuggestions();
    }

    public function updatedSearch()
    {
        if (strlen($this->search) >= 1) {
            $this->searchLocations();
            $this->showDropdown = true;
        } else {
            $this->searchResults = collect();
            $this->showDropdown = false;
            $this->showCreateOption = false;
        }
    }

    public function searchLocations()
    {
        // Search in local database first
        $localResults = Location::locationSearch($this->search)
            ->frecencyOrder()
            ->limit(5)
            ->get();

        $this->searchResults = $localResults;

        // Show option to create new location if no exact match
        $exactMatch = $localResults->firstWhere('code', $this->search)
                   || $localResults->firstWhere('name', $this->search);
        $this->showCreateOption = ! $exactMatch && strlen($this->search) >= 2;
    }

    public function selectLocation($locationId, $code = null, $name = null)
    {
        $this->selectedLocationId = $locationId;

        if ($locationId === 'create') {
            // Create new location
            $this->createLocation($this->search);
        } else {
            // Find location and record usage
            $location = Location::where('location_id', $locationId)->first();
            if ($location) {
                $location->recordUsage();
                $this->search = $location->display_name;
            }
        }

        $this->showDropdown = false;
        $this->errorMessage = '';

        // Emit event for parent components - include location code for stock movement form
        $this->dispatch('locationSelected', $this->selectedLocationId, $code ?? $this->search, $this->type);
    }

    public function createLocation(string $code)
    {
        try {
            // Create a simple location entry
            $location = Location::create([
                'location_id' => 'local_'.uniqid(), // Temporary ID for local-only locations
                'code' => $code,
                'name' => $code,
                'use_count' => 1,
                'last_used_at' => now(),
                'is_active' => true,
            ]);

            $this->selectedLocationId = $location->location_id;
            $this->search = $location->display_name;

            $this->loadSmartSuggestions(); // Refresh suggestions

            $this->dispatch('locationCreated', $location->id, $location->code);
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to create location: '.$e->getMessage();
        }
    }

    public function loadSmartSuggestions()
    {
        // Get top frecency locations for quick selection
        $this->smartSuggestions = Location::frecencyOrder()
            ->limit(6)
            ->get();
    }

    public function clearSelection()
    {
        $this->selectedLocationId = '';
        $this->search = '';
        $this->showDropdown = false;
        $this->dispatch('locationCleared');
    }

    public function showSuggestions()
    {
        $this->showDropdown = true;
    }

    public function hideSuggestions()
    {
        // Delay hiding to allow for clicks
        $this->dispatch('hideDropdown');
    }

    #[On('hideDropdown')]
    public function hideDropdown()
    {
        $this->showDropdown = false;
    }

    /**
     * Sync locations from Linnworks (useful for initial setup)
     */
    public function syncFromLinnworks()
    {
        try {
            $linnworksService = app(LinnworksApiService::class);
            $locations = $linnworksService->getLocations();

            $synced = 0;
            foreach ($locations as $locationData) {
                $locationId = $locationData['StockLocationId'] ?? $locationData['LocationId'] ?? null;
                $locationName = $locationData['LocationName'] ?? $locationData['Name'] ?? 'Unknown';

                if ($locationId) {
                    Location::createOrUpdateFromLinnworks($locationId, $locationName);
                    $synced++;
                }
            }

            $this->loadSmartSuggestions();
            $this->dispatch('notify', "Synced {$synced} locations from Linnworks");

        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to sync from Linnworks: '.$e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.smart-location-selector');
    }
}
