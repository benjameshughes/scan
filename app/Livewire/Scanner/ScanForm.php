<?php

namespace App\Livewire\Scanner;

use App\Actions\Scanner\CreateScanRecordAction;
use App\DTOs\Scanner\ScanData;
use App\Livewire\Forms\ScanFormData;
use App\Models\Product;
use App\Services\Scanner\UserFeedbackService;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ScanForm extends Component
{
    public ScanFormData $form;

    public ?Product $product = null;

    public function mount(
        ?string $barcode = null,
        ?Product $product = null,
    ) {
        $this->form->setBarcode($barcode);
        $this->product = $product;
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

        // Create scan data DTO
        $scanData = ScanData::fromForm(
            barcode: $this->form->barcode,
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
        $this->dispatch('empty-bay-notification', [
            'barcode' => $this->form->barcode,
        ]);
    }

    public function render()
    {
        return view('livewire.scanner.scan-form');
    }
}
