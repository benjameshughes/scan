<?php

namespace App\Livewire\Scanner\Concerns;

use App\Models\Product;
use Livewire\Attributes\On;

/**
 * Manages scan state and barcode handling
 *
 * This trait handles all scan-related state properties and operations including:
 * - Barcode detection and processing
 * - Product association
 * - State reset operations
 * - State array application
 *
 * @requires HasCameraState for $isScanning, $cameraError, $isTorchOn properties
 * @requires HasChildComponentEvents for $showRefillForm, $showEmptyBayNotification properties
 * @requires HasAutoSubmit for $autoSubmitEnabled property and handleAutoSubmit() method
 * @requires InteractsWithScannerServices for service access
 */
trait HasScanState
{
    use InteractsWithScannerServices;

    // Scan state properties
    public ?string $barcode = null;

    public bool $barcodeScanned = false;

    public ?Product $product = null;

    /**
     * Handle barcode detection from JavaScript/Alpine
     *
     * This is the main entry point for barcode scanning from the camera.
     * It processes the barcode, updates state, triggers feedback, and initiates
     * auto-submit if enabled.
     */
    #[On('onBarcodeDetected')]
    public function handleBarcodeDetected(string $barcode): void
    {
        // Process the barcode using the action
        $result = $this->processBarcodeAction()->handleCameraDetection($barcode);

        if ($result->isValid) {
            $this->barcode = $result->barcode;
            $this->barcodeScanned = true;
            $this->product = $result->product;
            $this->isScanning = false;

            // Close any open forms when new barcode is scanned
            $this->showRefillForm = false;
            $this->showEmptyBayNotification = false;

            // Trigger feedback if needed
            if ($result->shouldTriggerFeedback) {
                $feedbackState = $this->userFeedbackService()->triggerScanFeedback(
                    $result->product,
                    auth()->user()
                );

                if ($feedbackState['playSuccessSound']) {
                    $this->dispatch('play-success-sound');
                }

                if ($feedbackState['triggerVibration']) {
                    $vibrationData = $this->userFeedbackService()->getVibrationPatternData(auth()->user());
                    $this->dispatch('trigger-vibration', $vibrationData);
                }
            }

            // Check if auto-submit should be triggered
            if ($this->autoSubmitScanAction()->shouldAutoSubmit($this->product, $this->autoSubmitEnabled, true)) {
                $this->handleAutoSubmit();
            }
        } else {
            $this->cameraError = $result->error;
            $this->isTorchOn = false; // Reset torch state when error occurs
        }
    }

    /**
     * Reset for new scan
     */
    protected function resetForNewScan(): void
    {
        $this->barcode = null;
        $this->barcodeScanned = false;
        $this->product = null;
        $this->cameraError = '';
        $this->showRefillForm = false;
        $this->showEmptyBayNotification = false;
        $this->resetValidation();
    }

    /**
     * Reset state after scan submission
     */
    protected function resetAfterScanSubmission(): void
    {
        $this->barcode = null;
        $this->barcodeScanned = false;
        $this->product = null;
        $this->showRefillForm = false;
        $this->showEmptyBayNotification = false;
        $this->autoSubmitInProgress = false;
        $this->cameraError = '';
        $this->resetValidation();
    }

    /**
     * Reset state after empty bay submission
     */
    protected function resetAfterEmptyBaySubmission(): void
    {
        $this->barcode = null;
        $this->barcodeScanned = false;
        $this->product = null;
        $this->showEmptyBayNotification = false;
        $this->cameraError = '';
        $this->resetValidation();
    }

    /**
     * Apply an array of state changes to component properties
     *
     * This method is used to apply state changes from actions and services.
     * It handles special cases like product model loading and validation reset.
     */
    protected function applyStateArray(array $stateArray): void
    {
        foreach ($stateArray as $property => $value) {
            if (property_exists($this, $property) && $property !== 'resetValidation' && $property !== 'dispatchEvent') {
                // Special handling for product property - ensure it's a Product model or null
                if ($property === 'product') {
                    if ($value === null) {
                        $this->product = null;
                    } elseif ($value instanceof Product) {
                        $this->product = $value;
                    } elseif (is_array($value) && isset($value['id'])) {
                        $this->product = Product::find($value['id']);
                    } else {
                        $this->product = null;
                    }
                } else {
                    $this->$property = $value;
                }
            }
        }

        // Handle validation reset
        if (isset($stateArray['resetValidation'])) {
            if ($stateArray['resetValidation'] === true) {
                $this->resetValidation();
            } elseif (is_array($stateArray['resetValidation'])) {
                $this->resetValidation($stateArray['resetValidation']);
            }
        }
    }
}
