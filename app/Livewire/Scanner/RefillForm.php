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

    #[Validate('required|string')]
    public string $fromLocationId = '';

    #[Validate('required|string')]
    public string $toLocationId;

    #[Validate('required|integer|min:1')]
    public int $refillQuantity = 1;

    public array $availableLocations = [];

    public array $allLocations = [];

    public string $searchFrom = '';

    public string $searchTo = '';

    public bool $isProcessingRefill = false;

    public string $refillError = '';

    // Deprecated: kept for backward compatibility during transition
    public string $selectedLocationId = '';

    public function mount(
        ?Product $product = null,
    ) {
        $this->product = $product;

        // Initialize toLocationId with default location BEFORE preparing form
        $this->toLocationId = config('linnworks.default_location_id');

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
            $this->allLocations = $result['allLocations'];
            $this->fromLocationId = $result['selectedLocationId'];
            $this->toLocationId = $result['toLocationId'];
            // Keep for backward compatibility
            $this->selectedLocationId = $result['selectedLocationId'];
        } else {
            $this->refillError = $result['error'];
        }

        $this->isProcessingRefill = false;
    }

    /**
     * Handle location selection from smart location selector
     *
     * @deprecated Use fromLocationId directly
     */
    #[On('locationChanged')]
    public function onLocationChanged($locationId): void
    {
        $result = app(PrepareRefillFormAction::class)->handleLocationChange($locationId, $this->availableLocations);

        if ($result['success']) {
            $this->selectedLocationId = $result['selectedLocationId'];
            $this->fromLocationId = $result['selectedLocationId'];
            $this->refillQuantity = $result['refillQuantity'];
            $this->resetValidation(['selectedLocationId', 'fromLocationId']);
        } else {
            $this->addError('selectedLocationId', $result['error']);
        }
    }

    /**
     * Get filtered "From" locations based on search
     */
    #[Computed]
    public function filteredFromLocations(): array
    {
        if (empty($this->availableLocations)) {
            return [];
        }

        return app(PrepareRefillFormAction::class)->filterLocationsBySearch(
            $this->availableLocations,
            $this->searchFrom
        );
    }

    /**
     * Get filtered "To" locations based on search (ALL locations regardless of stock)
     */
    #[Computed]
    public function filteredToLocations(): array
    {
        if (empty($this->allLocations)) {
            return [];
        }

        return app(PrepareRefillFormAction::class)->filterAllLocationsBySearch(
            $this->allLocations,
            $this->searchTo
        );
    }

    /**
     * Validate refill quantity when updated
     */
    public function updatedRefillQuantity()
    {
        $locationId = $this->fromLocationId ?: $this->selectedLocationId;

        if (! $locationId || empty($this->availableLocations)) {
            return;
        }

        $validation = app(PrepareRefillFormAction::class)->validateRefillQuantity(
            $this->refillQuantity,
            $locationId,
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
        $locationId = $this->fromLocationId ?: $this->selectedLocationId;

        if (! $locationId || empty($this->availableLocations)) {
            return 0;
        }

        return app(PrepareRefillFormAction::class)->getMaxRefillStock(
            $locationId,
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
        $locationId = $this->fromLocationId ?: $this->selectedLocationId;

        $newQuantity = app(PrepareRefillFormAction::class)->incrementRefillQuantity(
            $this->refillQuantity,
            $locationId,
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
                selectedLocationId: $this->fromLocationId ?: $this->selectedLocationId,
                toLocationId: $this->toLocationId,
                refillQuantity: $this->refillQuantity,
                user: auth()->user()
            );

            if ($result['success']) {
                // Notify parent and immediately close - no success message needed
                $this->dispatch('refill-submitted', [
                    'product_sku' => $this->product->sku,
                ]);
                $this->cancelRefill();

                return;
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
            'refillQuantity' => 'required|integer|min:1|max:'.$this->maxRefillStock,
            // Keep for backward compatibility
            'selectedLocationId' => 'nullable|string',
        ];
    }

    public function getMessages()
    {
        return [
            'fromLocationId.required' => 'Please select a location to transfer from.',
            'fromLocationId.different' => 'The from and to locations must be different.',
            'toLocationId.required' => 'Please select a location to transfer to.',
            'toLocationId.different' => 'The from and to locations must be different.',
            'refillQuantity.required' => 'Please enter a quantity to transfer.',
            'refillQuantity.min' => 'Quantity must be at least 1.',
            'refillQuantity.max' => 'Quantity exceeds available stock.',
            // Keep for backward compatibility
            'selectedLocationId.required' => 'Please select a location to transfer from.',
        ];
    }

    public function render()
    {
        return view('livewire.scanner.refill-form');
    }
}
