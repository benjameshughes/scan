<?php

namespace App\Livewire;

use App\Models\Location;
use App\Services\LinnworksApiService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class LocationsTable extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $sortField = 'use_count';

    #[Url]
    public string $sortDirection = 'desc';

    #[Url]
    public bool $showInactive = false;

    // Processing state
    public bool $isProcessingSync = false;

    // Edit form state
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

    public function sort(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedShowInactive(): void
    {
        $this->resetPage();
    }

    // Edit actions
    public function edit(int $id): void
    {
        $location = Location::findOrFail($id);

        $this->editingLocationId = $location->id;
        $this->editCode = $location->code;
        $this->editName = $location->name ?? '';
        $this->editQrCode = $location->qr_code ?? '';
        $this->editIsActive = $location->is_active;

        $this->resetValidation();
        $this->dispatch('open-modal', 'edit-location');
    }

    public function saveLocation(): void
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

            session()->flash('message', "Location '{$location->code}' updated successfully.");
            $this->dispatch('close-modal', 'edit-location');
            $this->resetEditForm();

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update location: '.$e->getMessage());
        }
    }

    public function cancelEdit(): void
    {
        $this->resetEditForm();
        $this->dispatch('close-modal', 'edit-location');
    }

    private function resetEditForm(): void
    {
        $this->editingLocationId = null;
        $this->editCode = '';
        $this->editName = '';
        $this->editQrCode = '';
        $this->editIsActive = true;
        $this->resetValidation();
    }

    public function toggle(int $id): void
    {
        try {
            $location = Location::findOrFail($id);
            $location->update(['is_active' => ! $location->is_active]);

            $status = $location->is_active ? 'activated' : 'deactivated';
            session()->flash('message', "Location '{$location->code}' {$status} successfully.");

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update location status: '.$e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        try {
            $location = Location::findOrFail($id);
            $locationCode = $location->code;

            $location->delete();

            session()->flash('message', "Location '{$locationCode}' deleted successfully.");

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete location: '.$e->getMessage());
        }
    }

    public function syncFromLinnworks(): void
    {
        $this->isProcessingSync = true;

        try {
            $linnworksService = app(LinnworksApiService::class);
            $locations = $linnworksService->getLocations();

            $synced = 0;
            $updated = 0;

            foreach ($locations as $locationData) {
                $locationId = $locationData['StockLocationId']
                    ?? $locationData['LocationId']
                    ?? $locationData['Id']
                    ?? null;

                $locationName = $locationData['LocationName']
                    ?? $locationData['Name']
                    ?? $locationData['BinRack']
                    ?? "Location {$locationId}";

                if ($locationId) {
                    $location = Location::createOrUpdateFromLinnworks($locationId, $locationName);

                    if ($location->wasRecentlyCreated) {
                        $synced++;
                    } else {
                        $updated++;
                    }

                    Log::channel('inventory')->info('Processed location', [
                        'location_id' => $locationId,
                        'location_name' => $locationName,
                        'action' => $location->wasRecentlyCreated ? 'created' : 'updated',
                    ]);
                }
            }

            session()->flash('message', "Sync completed: {$synced} new locations created, {$updated} locations updated.");

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to sync from Linnworks: '.$e->getMessage());
        } finally {
            $this->isProcessingSync = false;
        }
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'total' => Location::count(),
            'active' => Location::where('is_active', true)->count(),
            'recently_used' => Location::whereNotNull('last_used_at')
                ->where('last_used_at', '>=', now()->subDays(30))
                ->count(),
        ];
    }

    protected function getQuery()
    {
        return Location::query()
            ->when($this->search, fn ($q) => $q->where(function ($query) {
                $query->where('code', 'like', "%{$this->search}%")
                    ->orWhere('name', 'like', "%{$this->search}%")
                    ->orWhere('qr_code', 'like', "%{$this->search}%");
            }))
            ->when(! $this->showInactive, fn ($q) => $q->where('is_active', true))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        $locations = $this->getQuery()->paginate(15);

        return view('livewire.locations-table', [
            'locations' => $locations,
        ]);
    }
}
