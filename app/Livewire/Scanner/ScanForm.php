<?php

namespace App\Livewire\Scanner;

use App\Actions\Scanner\CreateScanRecordAction;
use App\DTOs\EmptyBayDTO;
use App\DTOs\Scanner\ScanData;
use App\Jobs\EmptyBayJob;
use App\Livewire\Forms\ScanFormData;
use App\Models\Product;
use App\Services\LinnworksApiService;
use App\Services\Scanner\UserFeedbackService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ScanForm extends Component
{
    public ScanFormData $form;

    public ?Product $product = null;

    public ?int $defaultLocationStock = null;

    public function mount(
        ?string $barcode = null,
        ?Product $product = null,
    ) {
        $this->form->setBarcode($barcode);
        $this->product = $product;

        $this->fetchDefaultLocationStock();
    }

    private function fetchDefaultLocationStock(): void
    {
        if (! $this->product?->sku) {
            return;
        }

        try {
            $linnworks = app(LinnworksApiService::class);
            $this->defaultLocationStock = $linnworks->getStockLevel($this->product->sku);
        } catch (\Throwable $e) {
            Log::channel('inventory')->warning('Failed to fetch stock level for scan form', [
                'sku' => $this->product->sku,
                'error' => $e->getMessage(),
            ]);
            $this->defaultLocationStock = null;
        }
    }

    /**
     * Real-time validation when form fields update
     */
    public function updated(string $property): void
    {
        // Only validate form properties
        if (str_starts_with($property, 'form.')) {
            $field = str_replace('form.', '', $property);
            $this->form->validateOnly($field);
        }
    }

    public function incrementQuantity(): void
    {
        $this->form->incrementQuantity();
    }

    public function decrementQuantity(): void
    {
        $this->form->decrementQuantity();
    }

    public function save(): void
    {
        // Validate all form fields
        $this->form->validate();

        $isDecrease = ! $this->form->scanAction;

        if ($isDecrease && $this->defaultLocationStock !== null) {
            if ($this->defaultLocationStock === 0) {
                $this->addError('form', 'Stock is already at zero on Default. Cannot decrease further.');

                return;
            }

            if ($this->form->quantity > $this->defaultLocationStock) {
                $this->addError('form', "Only {$this->defaultLocationStock} in stock on Default. Reduce quantity or restock first.");

                return;
            }
        }

        // Create scan data DTO — always use the product's barcode for storage
        $scanData = ScanData::fromForm(
            barcode: $this->product->barcode,
            quantity: $this->form->quantity,
            scanAction: $this->form->scanAction,
            userId: auth()->id()
        );

        try {
            // Create scan record
            $createScanRecordAction = app(CreateScanRecordAction::class);
            $scan = $createScanRecordAction->handle($scanData);

            // Trigger success feedback
            $userFeedbackService = app(UserFeedbackService::class);
            $feedbackState = $userFeedbackService->triggerSubmissionFeedback(auth()->user());

            // Notify parent component
            $this->dispatch('scan-submitted', [
                'scan_id' => $scan->id,
                'feedback' => $feedbackState,
            ]);

        } catch (ValidationException $e) {
            // Re-throw validation exceptions to show in form
            throw $e;
        } catch (\Exception $e) {
            $this->addError('form', 'Failed to submit scan: '.$e->getMessage());
        }
    }

    public function showRefillBayForm(): void
    {
        $this->dispatch('refill-form-requested');
    }

    public function emptyBayNotification(): void
    {
        // Dispatch notification job directly - no confirmation needed
        $emptyBayDTO = new EmptyBayDTO((int) $this->form->barcode);
        EmptyBayJob::dispatch($emptyBayDTO);

        // Reset scanner to ready state
        $this->dispatch('new-scan-requested');
    }

    public function render()
    {
        return view('livewire.scanner.scan-form');
    }
}
