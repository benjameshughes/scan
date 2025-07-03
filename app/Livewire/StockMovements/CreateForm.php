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
                ]
            ]);

            $this->success_message = 'Stock movement created successfully!';
            $this->error_message = '';
            
            // Redirect to the new movement using Livewire redirect
            $this->redirect(route('locations.movements.show', $movement), navigate: true);
            
        } catch (\Exception $e) {
            $this->error_message = 'Failed to create movement: ' . $e->getMessage();
            $this->success_message = '';
            
            // Log the full error for debugging
            \Log::error('Stock movement creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'product_id' => $this->product_id,
                'quantity' => $this->quantity,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function updatedProductSearch()
    {
        if (strlen($this->product_search) >= 2) {
            $product = Product::where('sku', 'like', '%' . $this->product_search . '%')
                              ->orWhere('name', 'like', '%' . $this->product_search . '%')
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
                           ->orderBy('code')
                           ->get()
                           ->map(function ($location) {
                               return [
                                   'id' => $location->location_id,
                                   'code' => $location->code,
                                   'name' => $location->name,
                               ];
                           });
        } catch (\Exception $e) {
            // Return empty array if there's an error
            return collect([]);
        }
    }

    public function render()
    {
        return view('livewire.stock-movements.create-form');
    }
}