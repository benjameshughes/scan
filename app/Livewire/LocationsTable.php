<?php

namespace App\Livewire;

use App\Models\Location;
use App\Services\LinnworksApiService;
use App\Tables\Columns\ActionsColumn;
use App\Tables\Columns\BadgeColumn;
use App\Tables\Columns\DateColumn;
use App\Tables\Columns\TextColumn;
use App\Tables\Table;
use App\Tables\TableComponent;

class LocationsTable extends TableComponent
{
    protected ?string $model = Location::class;
    protected ?string $title = 'Locations';
    protected array $searchable = ['code', 'name', 'qr_code'];

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

    public function table(Table $table): Table
    {
        return $table
            ->model(Location::class)
            ->title('Warehouse Locations')
            ->description('Manage your warehouse locations and settings')
            ->columns([
                TextColumn::make('code')
                    ->label('Location')
                    ->sortable()
                    ->searchable()
                    ->render(function ($record) {
                        $html = '<div>';
                        $html .= '<div class="text-sm font-medium text-gray-900 dark:text-gray-100">' . e($record->code) . '</div>';
                        
                        if ($record->name && $record->name !== $record->code) {
                            $html .= '<div class="text-xs text-zinc-500 dark:text-zinc-400">' . e($record->name) . '</div>';
                        }
                        
                        if ($record->qr_code) {
                            $html .= '<div class="flex items-center gap-1 mt-1">';
                            $html .= '<flux:icon.qr-code class="size-3 text-zinc-400 dark:text-zinc-500" />';
                            $html .= '<span class="text-xs text-zinc-400 dark:text-zinc-500 font-mono">' . e($record->qr_code) . '</span>';
                            $html .= '</div>';
                        }
                        
                        $html .= '</div>';
                        return $html;
                    }),

                TextColumn::make('use_count')
                    ->label('Usage Stats')
                    ->sortable()
                    ->render(function ($record) {
                        return '<div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                    <span class="flex items-center gap-1">
                                        <flux:icon.arrow-trending-up class="size-3" />
                                        ' . $record->use_count . ' uses
                                    </span>
                                </div>';
                    }),

                BadgeColumn::make('is_active')
                    ->label('Status')
                    ->sortable()
                    ->render(function ($record) {
                        $class = $record->is_active 
                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                            : 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-200';
                        
                        $text = $record->is_active ? 'Active' : 'Inactive';
                        
                        return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ' . $class . '">' . $text . '</span>';
                    }),

                DateColumn::make('last_used_at')
                    ->label('Last Used')
                    ->sortable()
                    ->render(function ($record) {
                        return '<span class="text-sm text-zinc-500 dark:text-zinc-400">' . 
                               ($record->last_used_at ? $record->last_used_at->diffForHumans() : 'Never') . 
                               '</span>';
                    }),

                ActionsColumn::make('actions')
                    ->label('Actions')
                    ->actions([
                        'edit' => [
                            'icon' => 'pencil',
                            'label' => 'Edit',
                            'variant' => 'ghost',
                            'size' => 'xs'
                        ],
                        'toggle' => [
                            'icon' => fn($record) => $record->is_active ? 'eye-slash' : 'eye',
                            'label' => fn($record) => $record->is_active ? 'Deactivate' : 'Activate',
                            'variant' => 'ghost',
                            'size' => 'xs'
                        ],
                        'delete' => [
                            'icon' => 'trash',
                            'label' => 'Delete',
                            'variant' => 'ghost',
                            'size' => 'xs',
                            'class' => 'text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300',
                            'confirm' => 'Are you sure you want to delete this location? This action cannot be undone.'
                        ]
                    ])
            ])
            ->headerActions([
                [
                    'action' => 'syncFromLinnworks',
                    'label' => 'Sync from Linnworks',
                    'icon' => 'arrow-path',
                    'variant' => 'filled',
                    'size' => 'sm',
                    'loading' => 'isProcessingSync'
                ]
            ])
            ->filters([
                [
                    'key' => 'show_inactive',
                    'label' => 'Show inactive',
                    'type' => 'boolean',
                    'default' => false,
                    'apply' => function ($query, $value) {
                        if (!$value) {
                            $query->where('is_active', true);
                        }
                    }
                ]
            ])
            ->defaultSort('use_count', 'desc')
            ->perPage(15)
            ->searchable(['code', 'name', 'qr_code']);
    }

    public function updatedShowInactiveLocations()
    {
        $this->filters['show_inactive'] = $this->showInactiveLocations;
        $this->resetPage();
    }

    protected function applyFilters($query)
    {
        parent::applyFilters($query);
        
        if (!$this->showInactiveLocations) {
            $query->where('is_active', true);
        }
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

    public function edit(int $id): void
    {
        $this->editLocation($id);
    }

    public function toggle(int $id): void
    {
        $this->toggleLocationStatus($id);
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

    public function delete(int $locationId): void
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
                // Handle multiple possible field names from different API responses
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
                    
                    \Log::channel('inventory')->info("Processed location", [
                        'location_id' => $locationId,
                        'location_name' => $locationName,
                        'action' => $location->wasRecentlyCreated ? 'created' : 'updated'
                    ]);
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

    public function getStatsProperty()
    {
        $totalLocations = Location::count();
        $activeLocations = Location::where('is_active', true)->count();
        $recentlyUsed = Location::whereNotNull('last_used_at')
            ->where('last_used_at', '>=', now()->subDays(30))
            ->count();

        return [
            'total' => $totalLocations,
            'active' => $activeLocations,
            'recently_used' => $recentlyUsed,
        ];
    }

    public function render()
    {
        return view('livewire.locations-table', [
            'data' => $this->getQuery()->paginate($this->perPage),
            'table' => $this->getTable(),
            'stats' => $this->getStatsProperty(),
        ]);
    }
}