<?php

namespace App\Actions\Scanner;

use App\Services\Scanner\CameraManagerService;
use Illuminate\Support\Facades\Log;

enum ResetContext: string
{
    case Initial = 'initial';
    case AfterSubmission = 'after_submission';
    case AfterRefill = 'after_refill';
    case AfterEmptyBay = 'after_empty_bay';
    case NewScan = 'new_scan';
}

class ResetScanStateAction
{
    public function __construct(
        private CameraManagerService $cameraManager,
    ) {}

    /**
     * Reset state based on context
     */
    public function reset(ResetContext $context): array
    {
        return match ($context) {
            ResetContext::Initial => $this->getInitialState(),
            ResetContext::AfterSubmission => $this->resetAfterSubmission(),
            ResetContext::AfterRefill => $this->resetAfterRefillSubmission(),
            ResetContext::AfterEmptyBay => $this->resetAfterEmptyBayNotification(),
            ResetContext::NewScan => $this->resetForNewScan(),
        };
    }

    /**
     * Reset all scan-related state to initial values
     */
    private function handle(): array
    {
        Log::debug('Resetting scan state');

        return [
            // Barcode and product state
            'barcode' => null,
            'barcodeScanned' => false,
            'product' => null,
            'quantity' => 1,

            // Error and feedback state
            'cameraError' => '',
            'playSuccessSound' => false,
            'triggerVibration' => false,

            // Email workflow state
            'isEmailRefill' => false,

            // Camera state (reset to initial "Ready to Scan")
            'loadingCamera' => false,
            'isScanning' => false,

            // Refill form state
            'showRefillForm' => false,
            'selectedLocationId' => '',
            'refillQuantity' => 1,
            'availableLocations' => [],
            'isProcessingRefill' => false,
            'refillError' => '',
            'refillSuccess' => '',

            // Validation state
            'resetValidation' => true,
        ];
    }

    /**
     * Reset scan state and prepare for new scan with camera restart
     */
    private function handleWithCameraRestart(): array
    {
        $resetState = $this->handle();

        // Prepare camera for new scan
        $cameraPreparation = $this->cameraManager->prepareForNewScan();

        Log::debug('Resetting scan state with camera restart');

        return array_merge($resetState, [
            'loadingCamera' => $cameraPreparation['state']->isLoading,
            'isScanning' => $cameraPreparation['state']->isScanning,
            'dispatchEvent' => $cameraPreparation['dispatchEvent'],
        ]);
    }

    /**
     * Get initial state for component mount
     */
    private function getInitialState(): array
    {
        $resetState = $this->handle();
        $cameraState = $this->cameraManager->getInitialState();

        return array_merge($resetState, [
            'loadingCamera' => $cameraState->isLoading,
            'isScanning' => $cameraState->isScanning,
            'torchSupported' => $cameraState->torchSupported,
            'isTorchOn' => $cameraState->torchEnabled,
        ]);
    }

    /**
     * Reset state after successful scan submission
     */
    private function resetAfterSubmission(): array
    {
        Log::info('Resetting state after successful scan submission');

        return $this->handleWithCameraRestart();
    }

    /**
     * Reset state after successful refill submission
     */
    private function resetAfterRefillSubmission(): array
    {
        Log::info('Resetting state after successful refill submission');

        return $this->handleWithCameraRestart();
    }

    /**
     * Reset state after empty bay notification
     */
    private function resetAfterEmptyBayNotification(): array
    {
        Log::info('Resetting state after empty bay notification');

        // Just reset scan state, no camera restart needed
        return $this->handle();
    }

    /**
     * Partial reset for "Scan Another" action
     */
    private function resetForNewScan(): array
    {
        Log::debug('Resetting for new scan');

        return [
            'barcode' => null,
            'barcodeScanned' => false,
            'product' => null,
            'quantity' => 1,
            'playSuccessSound' => false,
            'triggerVibration' => false,
            'cameraError' => '',
            'resetValidation' => true,
        ];
    }
}
