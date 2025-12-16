<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class RefillFormData extends Form
{
    #[Validate]
    public ?string $fromLocationId = null;

    #[Validate]
    public ?string $toLocationId = null;

    #[Validate]
    public ?int $refillQuantity = null;

    /**
     * Validation rules for the form
     */
    public function rules(): array
    {
        return [
            'fromLocationId' => [
                'required',
                'string',
                'different:toLocationId',
            ],
            'toLocationId' => [
                'required',
                'string',
                'different:fromLocationId',
            ],
            'refillQuantity' => 'required|integer|min:1',
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'fromLocationId.required' => 'Please select a location to transfer from.',
            'fromLocationId.different' => 'The from and to locations must be different.',
            'toLocationId.required' => 'Please select a location to transfer to.',
            'toLocationId.different' => 'The from and to locations must be different.',
            'refillQuantity.required' => 'Please enter a quantity to transfer.',
            'refillQuantity.min' => 'Quantity must be at least 1.',
        ];
    }

    /**
     * Custom attribute names for error messages
     */
    public function validationAttributes(): array
    {
        return [
            'fromLocationId' => 'from location',
            'toLocationId' => 'to location',
            'refillQuantity' => 'quantity',
        ];
    }

    /**
     * Increment quantity with max limit
     */
    public function incrementQuantity(int $max): void
    {
        $current = $this->refillQuantity ?? 0;
        if ($current < $max) {
            $this->refillQuantity = $current + 1;
        }
    }

    /**
     * Decrement quantity with validation
     */
    public function decrementQuantity(): void
    {
        $current = $this->refillQuantity ?? 0;
        if ($current > 1) {
            $this->refillQuantity = $current - 1;
        }
    }

    /**
     * Add to quantity (for quick select buttons)
     */
    public function addQuantity(int $amount, int $max): void
    {
        $current = $this->refillQuantity ?? 0;
        $this->refillQuantity = min($current + $amount, $max);
    }

    /**
     * Set to maximum quantity
     */
    public function setMaxQuantity(int $max): void
    {
        $this->refillQuantity = $max;
    }

    /**
     * Set from location ID
     */
    public function setFromLocationId(string $locationId): void
    {
        $this->fromLocationId = $locationId;
        $this->resetValidation(['fromLocationId']);
    }

    /**
     * Set to location ID
     */
    public function setToLocationId(string $locationId): void
    {
        $this->toLocationId = $locationId;
        $this->resetValidation(['toLocationId']);
    }

    /**
     * Set refill quantity
     */
    public function setRefillQuantity(int $quantity): void
    {
        $this->refillQuantity = $quantity;
        $this->resetValidation(['refillQuantity']);
    }

    /**
     * Reset form to defaults
     */
    public function resetForm(): void
    {
        $this->reset(['fromLocationId', 'toLocationId', 'refillQuantity']);
        $this->resetValidation();
    }
}
