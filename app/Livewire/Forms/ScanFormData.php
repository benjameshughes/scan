<?php

namespace App\Livewire\Forms;

use App\Rules\BarcodePrefixCheck;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ScanFormData extends Form
{
    #[Validate]
    public ?string $barcode = null;

    #[Validate]
    public ?int $quantity = 1;

    #[Validate]
    public bool $scanAction = false;

    /**
     * Validation rules for the form
     */
    public function rules(): array
    {
        return [
            'barcode' => ['required', 'string', new BarcodePrefixCheck('505903')],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'scanAction' => ['boolean'],
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'barcode.required' => 'A barcode is required to submit a scan.',
            'quantity.required' => 'Please enter a quantity.',
            'quantity.integer' => 'Quantity must be a whole number.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => 'Quantity cannot exceed 9999.',
        ];
    }

    /**
     * Custom attribute names for error messages
     */
    public function validationAttributes(): array
    {
        return [
            'barcode' => 'barcode',
            'quantity' => 'quantity',
            'scanAction' => 'stock action',
        ];
    }

    /**
     * Increment quantity with validation
     */
    public function incrementQuantity(): void
    {
        $current = $this->quantity ?? 1;
        if ($current < 9999) {
            $this->quantity = $current + 1;
            $this->validateOnly('quantity');
        }
    }

    /**
     * Decrement quantity with validation
     */
    public function decrementQuantity(): void
    {
        $current = $this->quantity ?? 1;
        if ($current > 1) {
            $this->quantity = $current - 1;
            $this->validateOnly('quantity');
        }
    }

    /**
     * Get the action string for database storage
     */
    public function getActionString(): string
    {
        return $this->scanAction ? 'increase' : 'decrease';
    }

    /**
     * Reset form to defaults
     */
    public function resetForm(): void
    {
        $this->reset(['quantity', 'scanAction']);
        $this->quantity = 1;
        $this->scanAction = false;
        $this->resetValidation();
    }

    /**
     * Set the barcode (typically from parent component)
     */
    public function setBarcode(?string $barcode): void
    {
        $this->barcode = $barcode;
    }
}
