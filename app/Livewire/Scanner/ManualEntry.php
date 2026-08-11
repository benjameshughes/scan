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

    public function updatedBarcode(ProcessBarcodeAction $processBarcodeAction, UserFeedbackService $userFeedbackService)
    {
        if (! $this->barcode) {
            $this->dispatch('barcode-processed', [
                'barcode' => null,
                'barcodeScanned' => false,
                'productId' => null,
            ]);

            return;
        }

        $this->resetValidation('barcode');

        $result = $processBarcodeAction->handleManualEntry($this->barcode);

        if (! $result->isValid) {
            $this->addError('barcode', $result->error);
            $this->dispatch('barcode-processed', ['barcode' => null, 'barcodeScanned' => false, 'productId' => null]);

            return;
        }

        if (! $result->hasProduct()) {
            $this->addError('barcode', 'No product found for this barcode/SKU.');

            return;
        }

        $this->barcodeScanned = true;

        $this->dispatch('barcode-processed', [
            'barcode' => $result->barcode,
            'barcodeScanned' => true,
            'productId' => $result->product->id,
        ]);

        if (! $result->shouldTriggerFeedback) {
            return;
        }

        $feedbackState = $userFeedbackService->triggerManualEntryFeedback($result->product, auth()->user());

        if ($feedbackState['playSuccessSound']) {
            $this->dispatch('play-success-sound');
        }

        if ($feedbackState['triggerVibration']) {
            $this->dispatch('trigger-vibration', $userFeedbackService->getVibrationPatternData(auth()->user()));
        }
    }

    public function render()
    {
        return view('livewire.scanner.manual-entry');
    }
}
