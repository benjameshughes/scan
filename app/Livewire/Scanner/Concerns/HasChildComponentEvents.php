<?php

namespace App\Livewire\Scanner\Concerns;

use App\Actions\Scanner\ResetContext;
use App\Models\Product;
use Livewire\Attributes\On;

/**
 * Manages child component event communication
 *
 * This trait handles all event communication with child components including:
 * - Product info component events
 * - Scan form events
 * - Refill form events
 * - Empty bay notification events
 * - Manual entry events
 */
trait HasChildComponentEvents
{
    use HasCameraState;
    use HasScanState;
    use InteractsWithScannerServices;

    // Child component visibility state
    public bool $showRefillForm = false;

    public bool $showEmptyBayNotification = false;

    /**
     * Handle barcode processed event from manual entry
     */
    #[On('barcode-processed')]
    public function onBarcodeProcessed(array $barcodeData): void
    {
        $this->barcode = $barcodeData['barcode'];
        $this->barcodeScanned = $barcodeData['barcodeScanned'];

        // Load product by ID if provided
        $this->product = isset($barcodeData['productId'])
            ? Product::find($barcodeData['productId'])
            : null;

        // Close any open forms when new barcode is processed
        $this->showRefillForm = false;
        $this->showEmptyBayNotification = false;

        // Stop camera hardware when barcode is processed
        $this->isScanning = false;
        $this->dispatch('camera-state-changed', false);
    }

    /**
     * Handle scan submitted event from scan form
     */
    #[On('scan-submitted')]
    public function onScanSubmitted(): void
    {
        // Reset state and restart camera after scan submission
        $resetState = $this->resetScanStateAction()->reset(ResetContext::AfterSubmission);
        $this->resetAfterScanSubmission();
        $this->applyStateArray($resetState);

        if (isset($resetState['dispatchEvent'])) {
            $this->dispatch(...$resetState['dispatchEvent']);
        }
    }

    /**
     * Handle refill form requested event from product info
     */
    #[On('refill-form-requested')]
    public function onRefillFormRequested(): void
    {
        $this->showRefillForm = true;
    }

    /**
     * Handle refill submitted event from refill form
     */
    #[On('refill-submitted')]
    public function onRefillSubmitted(): void
    {
        // Reset state and restart camera after refill submission
        $resetState = $this->resetScanStateAction()->reset(ResetContext::AfterRefill);
        $this->resetAfterScanSubmission();
        $this->applyStateArray($resetState);

        if (isset($resetState['dispatchEvent'])) {
            $this->dispatch(...$resetState['dispatchEvent']);
        }
    }

    /**
     * Handle refill cancelled event from refill form
     */
    #[On('refill-cancelled')]
    public function onRefillCancelled(): void
    {
        $this->showRefillForm = false;
    }

    /**
     * Handle empty bay notification event from product info
     */
    #[On('empty-bay-notification')]
    public function onEmptyBayNotification(): void
    {
        $this->showEmptyBayNotification = true;
    }

    /**
     * Handle empty bay submitted event from empty bay notification
     */
    #[On('empty-bay-submitted')]
    public function onEmptyBaySubmitted(): void
    {
        // Reset state and restart camera after empty bay submission
        $resetState = $this->resetScanStateAction()->reset(ResetContext::AfterSubmission);
        $this->resetAfterEmptyBaySubmission();
        $this->applyStateArray($resetState);

        if (isset($resetState['dispatchEvent'])) {
            $this->dispatch(...$resetState['dispatchEvent']);
        }
    }

    /**
     * Handle empty bay closed event from empty bay notification
     */
    #[On('empty-bay-closed')]
    public function onEmptyBayClosed(): void
    {
        $this->showEmptyBayNotification = false;
    }

    /**
     * Handle new scan requested event from product info
     */
    #[On('new-scan-requested')]
    public function onNewScanRequested(): void
    {
        // Reset for new scan and restart camera
        $this->resetForNewScan();

        // Start camera
        $currentState = $this->getCurrentCameraState();
        $cameraResult = $this->cameraManagerService()->handleToggle($currentState);
        $this->applyCameraState($cameraResult['state']);

        if (isset($cameraResult['dispatchEvent'])) {
            $this->dispatch(...$cameraResult['dispatchEvent']);
        }
    }

    /**
     * Handle reset sound flag event (delegated to child components)
     */
    #[On('reset-sound-flag')]
    public function resetSoundFlag(): void
    {
        // This will be handled by child components
    }

    /**
     * Handle reset vibration flag event (delegated to child components)
     */
    #[On('reset-vibration-flag')]
    public function resetVibrationFlag(): void
    {
        // This will be handled by child components
    }
}
