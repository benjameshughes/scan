<?php

namespace App\Livewire\Scanner;

use App\Actions\Scanner\PrepareRefillFormAction;
use App\Actions\Scanner\ProcessRefillSubmissionAction;
use App\Livewire\Forms\RefillFormData;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class RefillForm extends Component
{
    public ?Product $product = null;

    public RefillFormData $form;

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
        $this->form->toLocationId = config('linnworks.default_location_id');

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
            $this->form->fromLocationId = $result['selectedLocationId'];
            $this->form->toLocationId = $result['toLocationId'];
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
            $this->form->fromLocationId = $result['selectedLocationId'];
            $this->form->refillQuantity = $result['refillQuantity'];
            $this->form->resetValidation(['fromLocationId']);
            $this->resetValidation(['selectedLocationId']);
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
    public function updatedFormRefillQuantity(): void
    {
        // Skip validation if quantity is null (cleared)
        if ($this->form->refillQuantity === null) {
            $this->form->resetValidation(['refillQuantity']);

            return;
        }

        $locationId = $this->form->fromLocationId ?: $this->selectedLocationId;

        if (! $locationId || empty($this->availableLocations)) {
            return;
        }

        $validation = app(PrepareRefillFormAction::class)->validateRefillQuantity(
            $this->form->refillQuantity,
            $locationId,
            $this->availableLocations
        );

        if (! $validation['valid']) {
            $this->form->addError('refillQuantity', $validation['error']);
        } else {
            $this->form->resetValidation(['refillQuantity']);
        }
    }

    /**
     * Get the maximum stock available for the selected location
     */
    #[Computed]
    public function maxRefillStock(): int
    {
        $locationId = $this->form->fromLocationId ?: $this->selectedLocationId;

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
        $this->form->incrementQuantity($this->maxRefillStock());
    }

    /**
     * Decrement refill quantity
     */
    public function decrementRefillQuantity(): void
    {
        $this->form->decrementQuantity();
    }

    /**
     * Add to refill quantity (for quick select buttons)
     */
    public function addRefillQuantity(int $quantity): void
    {
        $this->form->addQuantity($quantity, $this->maxRefillStock());
    }

    /**
     * Set quantity to maximum available stock
     */
    public function setMaxRefillQuantity(): void
    {
        $this->form->setMaxQuantity($this->maxRefillStock());
    }

    /**
     * Submit the refill operation
     */
    public function submitRefill(): void
    {
        try {
            // Validate the form with dynamic max validation
            $this->form->validate([
                'fromLocationId' => [
                    'required',
                    'string',
                    'different:form.toLocationId',
                ],
                'toLocationId' => [
                    'required',
                    'string',
                    'different:form.fromLocationId',
                ],
                'refillQuantity' => 'required|integer|min:1|max:'.$this->maxRefillStock(),
            ]);

            // Set processing state
            $processingState = app(ProcessRefillSubmissionAction::class)->setProcessingState();
            $this->isProcessingRefill = $processingState['isProcessingRefill'];
            $this->refillError = $processingState['refillError'];

            // Process the refill submission
            $result = app(ProcessRefillSubmissionAction::class)->handle(
                product: $this->product,
                selectedLocationId: $this->form->fromLocationId ?: $this->selectedLocationId,
                toLocationId: $this->form->toLocationId,
                refillQuantity: $this->form->refillQuantity,
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

    public function render(): View
    {
        return view('livewire.scanner.refill-form');
    }
}
