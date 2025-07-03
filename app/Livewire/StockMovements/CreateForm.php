<?php

namespace App\Livewire\StockMovements;

use App\Models\Location;
use App\Models\Product;
use App\Models\StockMovement;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CreateForm extends Component
{
    #[Validate('required|exists:products,id')]
    public $product_id;

    #[Validate('required|integer|min:1')]
    public $quantity;

    #[Validate('nullable|string|max:500')]
    public $notes;

    #[Validate('required|in:manual_transfer,scan_adjustment')]
    public $type = 'manual_transfer';

    #[Validate('nullable|string|max:255')]
    public $from_location_code;

    #[Validate('nullable|string|max:255')]
    public $to_location_code;

    #[Validate('nullable|string|max:255')]
    public $from_location_id;

    #[Validate('nullable|string|max:255')]
    public $to_location_id;

    public $success_message = '';

    public $error_message = '';

    public $product_search = '';

    public $selected_product = null;

    public $show_location_suggestions = true;

    public $recently_used_locations = [];
    
    // Smart selector properties
    public $selectedProductId = '';
    public $selectedFromLocationId = '';
    public $selectedToLocationId = '';
    
    public $maxQuantity = null;
    public $currentStockLevel = null;

    public function mount()
    {
        // Check if user has permission to create stock movements
        if (! auth()->user()->can('create stock movements')) {
            abort(403, 'You do not have permission to create stock movements.');
        }
    }

    public function save()
    {
        $this->validate();

        try {
            $movement = StockMovement::create([
                'product_id' => $this->product_id,
                'from_location_id' => $this->from_location_id,
                'from_location_code' => $this->from_location_code,
                'to_location_id' => $this->to_location_id,
                'to_location_code' => $this->to_location_code,
                'quantity' => $this->quantity,
                'type' => $this->type,
                'user_id' => auth()->id(),
                'moved_at' => now(),
                'notes' => $this->notes,
                'metadata' => [
                    'manually_created' => true,
                    'created_by' => auth()->user()->name,
                ],
            ]);

            $this->success_message = 'Stock movement created successfully!';
            $this->error_message = '';

            // Redirect to the new movement using Livewire redirect
            $this->redirect(route('locations.movements.show', $movement), navigate: true);

        } catch (\Exception $e) {
            $this->error_message = 'Failed to create movement: '.$e->getMessage();
            $this->success_message = '';

            // Log the full error for debugging
            \Log::error('Stock movement creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'product_id' => $this->product_id,
                'quantity' => $this->quantity,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function updatedProductSearch()
    {
        if (strlen($this->product_search) >= 2) {
            $product = Product::where('sku', 'like', '%'.$this->product_search.'%')
                ->orWhere('name', 'like', '%'.$this->product_search.'%')
                ->first();

            if ($product) {
                $this->selected_product = $product;
                $this->product_id = $product->id;
            } else {
                $this->selected_product = null;
                $this->product_id = null;
            }
        } else {
            $this->selected_product = null;
            $this->product_id = null;
        }
    }

    public function clearProduct()
    {
        $this->product_search = '';
        $this->selected_product = null;
        $this->product_id = null;
    }

    public function selectFromLocation($locationCode, $locationId = null)
    {
        $this->from_location_code = $locationCode;
        $this->from_location_id = $locationId;
        $this->addToRecentlyUsed($locationCode, $locationId);
        $this->dispatch('location-selected', ['type' => 'from', 'code' => $locationCode]);
    }

    public function selectToLocation($locationCode, $locationId = null)
    {
        $this->to_location_code = $locationCode;
        $this->to_location_id = $locationId;
        $this->addToRecentlyUsed($locationCode, $locationId);
        $this->dispatch('location-selected', ['type' => 'to', 'code' => $locationCode]);
    }

    public function toggleLocationSuggestions()
    {
        $this->show_location_suggestions = ! $this->show_location_suggestions;
    }

    public function clearFromLocation()
    {
        $this->from_location_code = '';
        $this->from_location_id = '';
    }

    public function clearToLocation()
    {
        $this->to_location_code = '';
        $this->to_location_id = '';
    }

    public function swapLocations()
    {
        $tempCode = $this->from_location_code;
        $tempId = $this->from_location_id;

        $this->from_location_code = $this->to_location_code;
        $this->from_location_id = $this->to_location_id;

        $this->to_location_code = $tempCode;
        $this->to_location_id = $tempId;

        $this->dispatch('locations-swapped');
    }

    private function addToRecentlyUsed($locationCode, $locationId = null)
    {
        $location = ['code' => $locationCode, 'id' => $locationId];

        // Remove if already exists
        $this->recently_used_locations = array_filter(
            $this->recently_used_locations,
            fn ($loc) => $loc['code'] !== $locationCode
        );

        // Add to beginning
        array_unshift($this->recently_used_locations, $location);

        // Keep only last 5
        $this->recently_used_locations = array_slice($this->recently_used_locations, 0, 5);
    }

    public function getMovementTypesProperty()
    {
        return [
            StockMovement::TYPE_MANUAL_TRANSFER => 'Manual Transfer',
            StockMovement::TYPE_SCAN_ADJUSTMENT => 'Scan Adjustment',
        ];
    }

    public function getAvailableLocationsProperty()
    {
        try {
            return Location::where('is_active', true)
                ->orderBy('use_count', 'desc')
                ->orderBy('code')
                ->get()
                ->map(function ($location) {
                    return [
                        'id' => $location->location_id,
                        'code' => $location->code,
                        'name' => $location->name,
                        'use_count' => $location->use_count ?? 0,
                        'last_used' => $location->last_used_at ? $location->last_used_at->diffForHumans() : null,
                    ];
                });
        } catch (\Exception $e) {
            // Return empty array if there's an error
            return collect([]);
        }
    }

    public function getPopularLocationsProperty()
    {
        return $this->availableLocations->take(6);
    }

    public function getRecentLocationsProperty()
    {
        return collect($this->recently_used_locations);
    }
    

    #[On('productSelected')]
    public function onProductSelected($productId, $sku, $name)
    {
        $this->selectedProductId = $productId;
        $this->product_id = $productId;
        $this->selected_product = Product::find($productId);
        
        // Clear location selections when product changes
        $this->selectedFromLocationId = '';
        $this->from_location_id = '';
        $this->from_location_code = '';
        $this->selectedToLocationId = '';
        $this->to_location_id = '';
        $this->to_location_code = '';
        
        // Reset stock levels
        $this->currentStockLevel = null;
        $this->maxQuantity = null;
        $this->quantity = null;
        
        // Notify location selector to refresh
        $this->dispatch('productChanged', $productId);
    }
    
    #[On('productCleared')]
    public function onProductCleared()
    {
        $this->selectedProductId = '';
        $this->product_id = null;
        $this->selected_product = null;
        $this->maxQuantity = null;
        $this->currentStockLevel = null;
    }
    
    #[On('locationSelected')]
    public function onLocationSelected($locationId, $locationCode, $type = 'from')
    {
        if ($type === 'from') {
            $this->selectedFromLocationId = $locationId;
            $this->from_location_id = $locationId;
            $this->from_location_code = $locationCode;
            
            // Update stock levels when from location changes
            $this->updateStockLevels();
        } else {
            $this->selectedToLocationId = $locationId;
            $this->to_location_id = $locationId;
            $this->to_location_code = $locationCode;
        }
    }
    
    protected function updateStockLevels()
    {
        if ($this->selected_product && $this->from_location_code) {
            try {
                // Get stock locations for this product from Linnworks
                $linnworksService = app(\App\Services\LinnworksApiService::class);
                $stockLocations = $linnworksService->getStockLocationsByProduct($this->selected_product->sku);
                
                // Find the specific location
                $locationStock = collect($stockLocations)->first(function ($location) {
                    $locationData = $location['Location'] ?? [];
                    return ($locationData['LocationName'] ?? '') === $this->from_location_code;
                });
                
                if ($locationStock) {
                    $this->currentStockLevel = $locationStock['StockLevel'] ?? 0;
                    $this->maxQuantity = $this->currentStockLevel;
                } else {
                    $this->currentStockLevel = 0;
                    $this->maxQuantity = 0;
                }
            } catch (\Exception $e) {
                // Fallback to zero stock
                $this->currentStockLevel = 0;
                $this->maxQuantity = 0;
                \Log::warning('Failed to get stock level from Linnworks', [
                    'product_sku' => $this->selected_product->sku,
                    'location_code' => $this->from_location_code,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            $this->currentStockLevel = null;
            $this->maxQuantity = null;
        }
    }
    
    public function updatedQuantity()
    {
        if ($this->maxQuantity && $this->quantity > $this->maxQuantity) {
            $this->quantity = $this->maxQuantity;
            $this->error_message = "Quantity cannot exceed available stock ({$this->maxQuantity})";
        } else {
            $this->error_message = '';
        }
    }
    
    public function render()
    {
        return view('livewire.stock-movements.create-form');
    }
}
