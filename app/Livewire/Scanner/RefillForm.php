<?php

namespace App\Livewire\Scanner;

use App\Actions\Scanner\PrepareRefillFormAction;
use App\Actions\Scanner\ProcessRefillSubmissionAction;
use App\Models\Product;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class RefillForm extends Component
{
    public ?Product $product = null;

    public bool $isEmailRefill = false;

    #[Validate('required|string')]
    public string $selectedLocationId = '';

    #[Validate('required|integer|min:1')]
    public int $refillQuantity = 1;

    public array $availableLocations = [];

    public bool $isProcessingRefill = false;

    public string $refillError = '';

    public string $refillSuccess = '';

    public function mount(
        ?Product $product = null,
        bool $isEmailRefill = false,
    ) {
        $this->product = $product;
        $this->isEmailRefill = $isEmailRefill;

        // Prepare the form when component is mounted
        if ($this->product) {
            $this->prepareForm();
        }
    }

    /**
     * Prepare the refill form with locations
     */
    public function prepareForm()
    {
        if (! $this->product) {
            $this->refillError = 'No product selected for refill.';

            return;
        }

        $this->isProcessingRefill = true;
        $this->refillError = '';

        $prepareRefillFormAction = app(PrepareRefillFormAction::class);
        $result = $prepareRefillFormAction->handle($this->product, auth()->user());

        if ($result['success']) {
            $this->availableLocations = $result['availableLocations'];
            $this->selectedLocationId = $result['selectedLocationId'];
        } else {
            $this->refillError = $result['error'];
        }

        $this->isProcessingRefill = false;
    }

    /**
     * Handle location selection from smart location selector
     */
    #[On('locationChanged')]
    public function onLocationChanged($locationId): void
    {
        $result = app(PrepareRefillFormAction::class)->handleLocationChange($locationId, $this->availableLocations);

        if ($result['success']) {
            $this->selectedLocationId = $result['selectedLocationId'];
            $this->refillQuantity = $result['refillQuantity'];
            $this->resetValidation(['selectedLocationId']);
        } else {
            $this->addError('selectedLocationId', $result['error']);
        }
    }

    /**
     * Validate refill quantity when updated
     */
    public function updatedRefillQuantity()
    {
        if (! $this->selectedLocationId || empty($this->availableLocations)) {
            return;
        }

        $validation = app(PrepareRefillFormAction::class)->validateRefillQuantity(
            $this->refillQuantity,
            $this->selectedLocationId,
            $this->availableLocations
        );

        if (! $validation['valid']) {
            $this->addError('refillQuantity', $validation['error']);
        } else {
            $this->resetValidation(['refillQuantity']);
        }
    }

    /**
     * Get the maximum stock available for the selected location
     */
    #[Computed]
    public function maxRefillStock(): int
    {
        if (! $this->selectedLocationId || empty($this->availableLocations)) {
            return 0;
        }

        return app(PrepareRefillFormAction::class)->getMaxRefillStock(
            $this->selectedLocationId,
            $this->availableLocations
        );
    }

    /**
     * Get formatted locations for the smart location selector
     */
    #[Computed]
    public function smartLocationSelectorData(): array
    {
        return app(PrepareRefillFormAction::class)->getSmartLocationSelectorData($this->availableLocations);
    }

    /**
     * Increment refill quantity
     */
    public function incrementRefillQuantity(): void
    {
        $newQuantity = app(PrepareRefillFormAction::class)->incrementRefillQuantity(
            $this->refillQuantity,
            $this->selectedLocationId,
            $this->availableLocations
        );

        if ($newQuantity !== $this->refillQuantity) {
            $this->refillQuantity = $newQuantity;
            $this->resetValidation(['refillQuantity']);
        }
    }

    /**
     * Decrement refill quantity
     */
    public function decrementRefillQuantity(): void
    {
        $newQuantity = app(PrepareRefillFormAction::class)->decrementRefillQuantity($this->refillQuantity);

        if ($newQuantity !== $this->refillQuantity) {
            $this->refillQuantity = $newQuantity;
            $this->resetValidation(['refillQuantity']);
        }
    }

    /**
     * Submit the refill operation
     */
    public function submitRefill(): void
    {
        try {
            // Set processing state
            $processingState = app(ProcessRefillSubmissionAction::class)->setProcessingState();
            $this->isProcessingRefill = $processingState['isProcessingRefill'];
            $this->refillError = $processingState['refillError'];

            // Process the refill submission
            $result = app(ProcessRefillSubmissionAction::class)->handle(
                product: $this->product,
                selectedLocationId: $this->selectedLocationId,
                refillQuantity: $this->refillQuantity,
                user: auth()->user()
            );

            if ($result['success']) {
                // Prepare success state
                $successState = app(ProcessRefillSubmissionAction::class)->prepareSuccessState($result['message']);
                $this->applyState($successState);

                // Notify parent component
                $this->dispatch('refill-submitted', [
                    'message' => $result['message'],
                    'product_sku' => $this->product->sku,
                ]);
            } else {
                // Prepare error state
                $errorState = app(ProcessRefillSubmissionAction::class)->prepareErrorState($result['error']);
                $this->applyState($errorState);
            }

        } catch (ValidationException $e) {
            $this->isProcessingRefill = false;
            throw $e;
        } catch (\Exception $e) {
            $errorState = app(ProcessRefillSubmissionAction::class)->prepareErrorState($e->getMessage());
            $this->applyState($errorState);
        }
    }

    /**
     * Cancel the refill operation and return to scanner
     */
    public function cancelRefill(): void
    {
        $this->dispatch('refill-cancelled');
    }

    /**
     * Clear refill error message
     */
    public function clearRefillError(): void
    {
        $clearState = app(PrepareRefillFormAction::class)->clearRefillError();
        $this->applyState($clearState);
    }

    /**
     * Apply state array to component properties
     */
    private function applyState(array $state): void
    {
        foreach ($state as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }

    public function getRules()
    {
        return [
            'selectedLocationId' => 'required|string',
            'refillQuantity' => 'required|integer|min:1|max:'.$this->maxRefillStock,
        ];
    }

    public function getMessages()
    {
        return [
            'selectedLocationId.required' => 'Please select a location to transfer from.',
            'refillQuantity.required' => 'Please enter a quantity to transfer.',
            'refillQuantity.min' => 'Quantity must be at least 1.',
            'refillQuantity.max' => 'Quantity exceeds available stock.',
        ];
    }

    public function render()
    {
        return view('livewire.scanner.refill-form');
    }
}
