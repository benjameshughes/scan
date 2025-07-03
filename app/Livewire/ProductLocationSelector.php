<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\LinnworksApiService;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductLocationSelector extends Component
{
    public string $search = '';
    
    public string $selectedLocationId = '';
    
    public string $selectedLocationCode = '';
    
    public string $placeholder = 'Select location with stock...';
    
    public bool $showDropdown = false;
    
    public bool $required = false;
    
    public string $label = 'From Location';
    
    public string $errorMessage = '';
    
    public ?Product $product = null;
    
    public string $productId = '';
    
    // Component properties
    public Collection $productLocations;
    
    public Collection $searchResults;
    
    public function mount()
    {
        $this->productLocations = collect();
        $this->searchResults = collect();
        
        if ($this->productId) {
            $this->product = Product::find($this->productId);
            $this->loadProductLocations();
        }
    }
    
    public function updatedProductId()
    {
        $this->product = Product::find($this->productId);
        $this->loadProductLocations();
        $this->clearSelection();
    }
    
    public function updatedSearch()
    {
        if (strlen($this->search) >= 1) {
            $this->searchLocations();
            $this->showDropdown = true;
        } else {
            $this->searchResults = collect();
            $this->showDropdown = false;
        }
    }
    
    public function searchLocations()
    {
        if ($this->productLocations->isEmpty()) {
            $this->searchResults = collect();
            return;
        }
        
        $this->searchResults = $this->productLocations->filter(function ($location) {
            return stripos($location['code'], $this->search) !== false ||
                   stripos($location['name'], $this->search) !== false;
        });
    }
    
    public function selectLocation($locationId, $locationCode, $quantity = 0)
    {
        $this->selectedLocationId = $locationId;
        $this->selectedLocationCode = $locationCode;
        $this->search = $locationCode . ' (' . $quantity . ' units)';
        $this->showDropdown = false;
        $this->errorMessage = '';
        
        // Emit event for parent components
        $this->dispatch('locationSelected', $locationId, $locationCode, 'from');
    }
    
    public function clearSelection()
    {
        $this->selectedLocationId = '';
        $this->selectedLocationCode = '';
        $this->search = '';
        $this->showDropdown = false;
        $this->dispatch('locationCleared');
    }
    
    public function showSuggestions()
    {
        if ($this->productLocations->isNotEmpty()) {
            $this->showDropdown = true;
        }
    }
    
    public function hideSuggestions()
    {
        // Delay hiding to allow for clicks
        $this->dispatch('hideDropdown');
    }
    
    public function hideDropdown()
    {
        $this->showDropdown = false;
    }
    
    #[On('productChanged')]
    public function onProductChanged($productId)
    {
        $this->productId = $productId;
        $this->product = Product::find($productId);
        $this->loadProductLocations();
        $this->clearSelection();
    }
    
    protected function loadProductLocations()
    {
        if (!$this->product) {
            $this->productLocations = collect();
            return;
        }
        
        try {
            // Get locations where this product has stock from Linnworks
            $linnworksService = app(LinnworksApiService::class);
            $stockLocations = $linnworksService->getStockLocationsByProduct($this->product->sku);
            
            $this->productLocations = collect($stockLocations)
                ->filter(function ($location) {
                    return isset($location['StockLevel']) && $location['StockLevel'] > 0;
                })
                ->map(function ($location) {
                    $locationData = $location['Location'] ?? [];
                    return [
                        'id' => $locationData['StockLocationId'] ?? $locationData['LocationName'] ?? 'unknown',
                        'code' => $locationData['LocationName'] ?? 'Unknown',
                        'name' => $locationData['LocationName'] ?? 'Unknown', 
                        'quantity' => $location['StockLevel'] ?? 0,
                        'available' => $location['Available'] ?? 0,
                    ];
                })
                ->sortByDesc('quantity')
                ->values();
                
        } catch (\Exception $e) {
            \Log::warning('Failed to get product locations from Linnworks', [
                'product_sku' => $this->product->sku,
                'error' => $e->getMessage()
            ]);
            
            $this->productLocations = collect();
            $this->errorMessage = 'Unable to load stock locations. Please try again.';
        }
    }
    
    public function render()
    {
        return view('livewire.product-location-selector');
    }
}