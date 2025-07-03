<?php

namespace App\Livewire\StockMovements;

use App\Models\StockMovement;
use Livewire\Attributes\Validate;
use Livewire\Component;

class EditForm extends Component
{
    public StockMovement $movement;

    #[Validate('required|integer|min:1')]
    public $quantity;

    #[Validate('nullable|string|max:500')]
    public $notes;

    #[Validate('required|in:bay_refill,manual_transfer,scan_adjustment')]
    public $type;

    #[Validate('nullable|string|max:255')]
    public $from_location_code;

    #[Validate('nullable|string|max:255')]
    public $to_location_code;

    public $success_message = '';

    public $error_message = '';

    public function mount(StockMovement $movement)
    {
        // Check if user has permission to edit stock movements
        if (! auth()->user()->can('edit stock movements')) {
            abort(403, 'You do not have permission to edit stock movements.');
        }

        $this->movement = $movement;
        $this->quantity = $movement->quantity;
        $this->notes = $movement->notes;
        $this->type = $movement->type;
        $this->from_location_code = $movement->from_location_code;
        $this->to_location_code = $movement->to_location_code;
    }

    public function save()
    {
        $this->validate();

        try {
            $this->movement->update([
                'quantity' => $this->quantity,
                'notes' => $this->notes,
                'type' => $this->type,
                'from_location_code' => $this->from_location_code,
                'to_location_code' => $this->to_location_code,
            ]);

            $this->success_message = 'Stock movement updated successfully!';
            $this->error_message = '';

            // Redirect after a short delay
            $this->dispatch('movement-updated');

        } catch (\Exception $e) {
            $this->error_message = 'Failed to update movement: '.$e->getMessage();
            $this->success_message = '';
        }
    }

    public function getMovementTypesProperty()
    {
        return [
            StockMovement::TYPE_BAY_REFILL => 'Bay Refill',
            StockMovement::TYPE_MANUAL_TRANSFER => 'Manual Transfer',
            StockMovement::TYPE_SCAN_ADJUSTMENT => 'Scan Adjustment',
        ];
    }

    public function render()
    {
        return view('livewire.stock-movements.edit-form');
    }
}
