<?php

namespace App\Livewire\Scanner;

use App\Actions\Scanner\ProcessBarcodeAction;
use App\Services\Scanner\UserFeedbackService;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ManualEntry extends Component
{
    #[Validate('nullable|string')]
    public ?string $barcode = null;

    public bool $barcodeScanned = false;

    public bool $showRefillForm = false;

    public function mount(
        ?string $barcode = null,
        bool $barcodeScanned = false,
        bool $showRefillForm = false,
    ) {
        $this->barcode = $barcode;
        $this->barcodeScanned = $barcodeScanned;
        $this->showRefillForm = $showRefillForm;
    }

    public function updatedBarcode()
    {
        if (! $this->barcode) {
            // Barcode was cleared - reset the scan state
            $this->dispatch('barcode-processed', [
                'barcode' => null,
                'barcodeScanned' => false,
                'productId' => null,
            ]);

            return;
        }

        // Clear any previous errors
        $this->resetValidation('barcode');

        try {
            // Process the barcode
            $processBarcodeAction = app(ProcessBarcodeAction::class);
            $result = $processBarcodeAction->handleManualEntry($this->barcode);

            if ($result->isValid) {
                // Valid barcode - dispatch to parent with product ID
                $this->dispatch('barcode-processed', [
                    'barcode' => $result->barcode,
                    'barcodeScanned' => true,
                    'productId' => $result->product?->id,
                ]);

                // Trigger user feedback if product found
                if ($result->shouldTriggerFeedback) {
                    $userFeedbackService = app(UserFeedbackService::class);
                    $feedbackState = $userFeedbackService->triggerManualEntryFeedback(
                        $result->product,
                        auth()->user()
                    );

                    if ($feedbackState['playSuccessSound']) {
                        $this->dispatch('play-success-sound');
                    }

                    if ($feedbackState['triggerVibration']) {
                        $vibrationData = $userFeedbackService->getVibrationPatternData(auth()->user());
                        $this->dispatch('trigger-vibration', $vibrationData);
                    }
                }
            } else {
                // Invalid barcode - show validation error
                $this->addError('barcode', $result->error);

                $this->dispatch('barcode-processed', [
                    'barcode' => null,
                    'barcodeScanned' => false,
                    'productId' => null,
                ]);
            }

        } catch (\Exception $e) {
            $this->addError('barcode', 'Error processing barcode: '.$e->getMessage());

            $this->dispatch('barcode-processed', [
                'barcode' => null,
                'barcodeScanned' => false,
                'productId' => null,
            ]);
        }
    }

    public function getRules()
    {
        return [
            'barcode' => 'nullable|string',
        ];
    }

    public function getMessages()
    {
        return [
            'barcode.string' => 'Barcode must be a valid string.',
        ];
    }

    public function render()
    {
        return view('livewire.scanner.manual-entry');
    }
}
