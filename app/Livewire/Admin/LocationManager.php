<?php

namespace App\Livewire\Admin;

use App\Models\Location;
use App\Services\LinnworksApiService;
use Livewire\Component;
use Livewire\WithPagination;

class LocationManager extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showInactiveLocations = false;
    public bool $isProcessingSync = false;
    public string $successMessage = '';
    public string $errorMessage = '';

    // Form state for editing
    public bool $showEditModal = false;
    public ?int $editingLocationId = null;
    public string $editCode = '';
    public string $editName = '';
    public string $editQrCode = '';
    public bool $editIsActive = true;

    protected $rules = [
        'editCode' => 'required|string|max:255',
        'editName' => 'nullable|string|max:255',
        'editQrCode' => 'nullable|string|max:255',
        'editIsActive' => 'boolean',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedShowInactiveLocations()
    {
        $this->resetPage();
    }

    public function editLocation($locationId)
    {
        $location = Location::findOrFail($locationId);
        
        $this->editingLocationId = $location->id;
        $this->editCode = $location->code;
        $this->editName = $location->name ?? '';
        $this->editQrCode = $location->qr_code ?? '';
        $this->editIsActive = $location->is_active;
        $this->showEditModal = true;
        
        $this->resetValidation();
    }

    public function saveLocation()
    {
        $this->validate();

        try {
            $location = Location::findOrFail($this->editingLocationId);
            
            $location->update([
                'code' => $this->editCode,
                'name' => $this->editName ?: null,
                'qr_code' => $this->editQrCode ?: null,
                'is_active' => $this->editIsActive,
            ]);

            $this->successMessage = "Location '{$location->code}' updated successfully.";
            $this->showEditModal = false;
            $this->resetEditForm();
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to update location: ' . $e->getMessage();
        }
    }

    public function cancelEdit()
    {
        $this->showEditModal = false;
        $this->resetEditForm();
    }

    private function resetEditForm()
    {
        $this->editingLocationId = null;
        $this->editCode = '';
        $this->editName = '';
        $this->editQrCode = '';
        $this->editIsActive = true;
        $this->resetValidation();
    }

    public function deleteLocation($locationId)
    {
        try {
            $location = Location::findOrFail($locationId);
            $locationCode = $location->code;
            
            $location->delete();
            
            $this->successMessage = "Location '{$locationCode}' deleted successfully.";
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to delete location: ' . $e->getMessage();
        }
    }

    public function toggleLocationStatus($locationId)
    {
        try {
            $location = Location::findOrFail($locationId);
            $location->update(['is_active' => !$location->is_active]);
            
            $status = $location->is_active ? 'activated' : 'deactivated';
            $this->successMessage = "Location '{$location->code}' {$status} successfully.";
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to update location status: ' . $e->getMessage();
        }
    }

    public function syncFromLinnworks()
    {
        $this->isProcessingSync = true;
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            $linnworksService = app(LinnworksApiService::class);
            $locations = $linnworksService->getLocations();
            
            $synced = 0;
            $updated = 0;
            
            foreach ($locations as $locationData) {
                $locationId = $locationData['StockLocationId'] ?? $locationData['LocationId'] ?? null;
                $locationName = $locationData['LocationName'] ?? $locationData['Name'] ?? 'Unknown';
                
                if ($locationId) {
                    $location = Location::createOrUpdateFromLinnworks($locationId, $locationName);
                    
                    if ($location->wasRecentlyCreated) {
                        $synced++;
                    } else {
                        $updated++;
                    }
                }
            }
            
            $this->successMessage = "Sync completed: {$synced} new locations created, {$updated} locations updated.";
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to sync from Linnworks: ' . $e->getMessage();
        } finally {
            $this->isProcessingSync = false;
        }
    }

    public function clearMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function render()
    {
        $query = Location::query();

        if ($this->search) {
            $query->search($this->search);
        }

        if (!$this->showInactiveLocations) {
            $query->where('is_active', true);
        }

        $locations = $query->orderBy('use_count', 'desc')
            ->orderBy('last_used_at', 'desc')
            ->paginate(15);

        $totalLocations = Location::count();
        $activeLocations = Location::where('is_active', true)->count();
        $recentlyUsed = Location::whereNotNull('last_used_at')
            ->where('last_used_at', '>=', now()->subDays(30))
            ->count();

        return view('livewire.admin.location-manager', [
            'locations' => $locations,
            'stats' => [
                'total' => $totalLocations,
                'active' => $activeLocations,
                'recently_used' => $recentlyUsed,
            ],
        ]);
    }
}
