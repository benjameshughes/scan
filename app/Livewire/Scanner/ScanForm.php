<?php

namespace App\Livewire\Scanner;

use App\Actions\Scanner\CreateScanRecordAction;
use App\Actions\Scanner\ValidateScanDataAction;
use App\DTOs\Scanner\ScanData;
use App\Models\Product;
use App\Services\Scanner\UserFeedbackService;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ScanForm extends Component
{
    public ?string $barcode = null;

    public ?Product $product = null;

    #[Validate('required|integer|min:1')]
    public int $quantity = 1;

    #[Validate('boolean')]
    public bool $scanAction = false;

    public bool $playSuccessSound = false;

    public bool $triggerVibration = false;

    public function mount(
        ?string $barcode = null,
        ?Product $product = null,
        int $quantity = 1,
        bool $scanAction = false,
        bool $playSuccessSound = false,
        bool $triggerVibration = false,
    ) {
        $this->barcode = $barcode;
        $this->product = $product;
        $this->quantity = $quantity;
        $this->scanAction = $scanAction;
        $this->playSuccessSound = $playSuccessSound;
        $this->triggerVibration = $triggerVibration;
    }

    public function incrementQuantity()
    {
        $this->quantity++;
        $this->resetValidation('quantity');
    }

    public function decrementQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
            $this->resetValidation('quantity');
        }
    }

    public function save()
    {
        // Create scan data DTO
        $scanData = ScanData::fromForm(
            barcode: $this->barcode,
            quantity: $this->quantity,
            scanAction: $this->scanAction,
            userId: auth()->id()
        );

        // Validate scan data
        $validateScanDataAction = app(ValidateScanDataAction::class);
        $validateScanDataAction->validateOrFail($scanData);

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

        } catch (\Exception $e) {
            $this->addError('form', 'Failed to submit scan: '.$e->getMessage());
        }
    }

    public function showRefillBayForm()
    {
        $this->dispatch('refill-form-requested');
    }

    public function emptyBayNotification()
    {
        $this->dispatch('empty-bay-notification', [
            'barcode' => $this->barcode,
        ]);
    }

    public function getRules()
    {
        return [
            'quantity' => 'required|integer|min:1',
            'scanAction' => 'boolean',
        ];
    }

    public function getMessages()
    {
        return [
            'quantity.integer' => 'Quantity must be an integer.',
            'quantity.min' => 'Quantity must be at least 1.',
        ];
    }

    public function render()
    {
        return view('livewire.scanner.scan-form');
    }
}
