<?php

namespace App\Livewire\StockMovements;

use App\Models\Location;
use App\Models\Product;
use App\Models\StockMovement;
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

    public function render()
    {
        return view('livewire.stock-movements.create-form');
    }
}
