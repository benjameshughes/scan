<?php

namespace App\Livewire\Scanner;

use App\Actions\Scanner\AutoSubmitScanAction;
use App\Actions\Scanner\ProcessBarcodeAction;
use App\Actions\Scanner\ResetContext;
use App\Actions\Scanner\ResetScanStateAction;
use App\DTOs\Scanner\CameraState;
use App\Models\Product;
use App\Services\Scanner\CameraManagerService;
use App\Services\Scanner\UserFeedbackService;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Product Scanner')]
class ProductScanner extends Component
{
    // Camera state
    public bool $isScanning = false;

    public bool $isTorchOn = false;

    public bool $torchSupported = false;

    public bool $loadingCamera = false;

    public string $cameraError = '';

    // Scan state
    public ?string $barcode = null;

    public bool $barcodeScanned = false;

    public ?Product $product = null;

    // Child component visibility state
    public bool $showRefillForm = false;

    public bool $showEmptyBayNotification = false;

    // Auto-submit state
    public bool $autoSubmitEnabled = false;

    public bool $autoSubmitInProgress = false;

    // Service getters
    private function resetScanStateAction(): ResetScanStateAction
    {
        return app(ResetScanStateAction::class);
    }

    private function cameraManagerService(): CameraManagerService
    {
        return app(CameraManagerService::class);
    }

    private function processBarcodeAction(): ProcessBarcodeAction
    {
        return app(ProcessBarcodeAction::class);
    }

    private function userFeedbackService(): UserFeedbackService
    {
        return app(UserFeedbackService::class);
    }

    private function autoSubmitScanAction(): AutoSubmitScanAction
    {
        return app(AutoSubmitScanAction::class);
    }

    public function mount()
    {
        // Ensure user is authenticated and has scanner permission
        if (! auth()->check()) {
            abort(401, 'Authentication required');
        }

        if (! auth()->user()->can('view scanner')) {
            abort(403, 'Insufficient permissions to use scanner');
        }

        // Initialize state using the reset action
        $initialState = $this->resetScanStateAction()->reset(ResetContext::Initial);
        $this->applyStateArray($initialState);

        // Load user settings for auto-submit
        $userSettings = auth()->user()->settings;
        $this->autoSubmitEnabled = $userSettings['auto_submit'] ?? false;
    }

    // Camera event handlers - delegate to service
    #[On('onCameraInitializing')]
    public function onCameraInitializing(): void
    {
        $cameraState = $this->cameraManagerService()->handleInitializing();
        $this->applyCameraState($cameraState);
    }

    #[On('onCameraReady')]
    public function onCameraReady(): void
    {
        $cameraState = $this->cameraManagerService()->handleReady();
        $this->applyCameraState($cameraState);
    }

    #[On('onCameraStopped')]
    public function onCameraStopped(): void
    {
        // Update UI state when camera is stopped (e.g., app backgrounded)
        $this->isScanning = false;
        $this->loadingCamera = false;
        $this->isTorchOn = false;
    }

    #[On('onCameraError')]
    public function onCameraError(string $error): void
    {
        $cameraState = $this->cameraManagerService()->handleError($error);
        $this->applyCameraState($cameraState);
    }

    #[On('onTorchSupportDetected')]
    public function onTorchSupportDetected(bool $supported): void
    {
        $currentState = $this->getCurrentCameraState();
        $cameraState = $this->cameraManagerService()->updateTorchSupport($currentState, $supported);
        $this->applyCameraState($cameraState);
    }

    #[On('onTorchStateChanged')]
    public function onTorchStateChanged(bool $enabled): void
    {
        $currentState = $this->getCurrentCameraState();
        $cameraState = $this->cameraManagerService()->updateTorchState($currentState, $enabled);
        $this->applyCameraState($cameraState);
    }

    // Barcode detection from JavaScript/Alpine
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
        }
    }

    // Child component event handlers
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

    #[On('refill-form-requested')]
    public function onRefillFormRequested(): void
    {
        $this->showRefillForm = true;
    }

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

    #[On('refill-cancelled')]
    public function onRefillCancelled(): void
    {
        $this->showRefillForm = false;
    }

    #[On('empty-bay-notification')]
    public function onEmptyBayNotification(): void
    {
        $this->showEmptyBayNotification = true;
    }

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

    #[On('empty-bay-closed')]
    public function onEmptyBayClosed(): void
    {
        $this->showEmptyBayNotification = false;
    }

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

    #[On('camera-toggle-requested')]
    public function onCameraToggleRequested(): void
    {
        $currentState = $this->getCurrentCameraState();
        $result = $this->cameraManagerService()->handleToggle($currentState);

        $this->applyCameraState($result['state']);

        if (isset($result['dispatchEvent'])) {
            $this->dispatch(...$result['dispatchEvent']);
        }
    }

    #[On('torch-toggle-requested')]
    public function onTorchToggleRequested(): void
    {
        $currentState = $this->getCurrentCameraState();
        $result = $this->cameraManagerService()->handleTorchToggle($currentState);

        if (isset($result['error'])) {
            $this->cameraError = $result['error'];
        } else {
            $this->applyCameraState($result['state']);

            if (isset($result['dispatchEvent'])) {
                $this->dispatch(...$result['dispatchEvent']);
            }
        }
    }

    #[On('error-cleared')]
    public function onErrorCleared(): void
    {
        $currentState = $this->getCurrentCameraState();
        $cameraState = $this->cameraManagerService()->clearError($currentState);
        $this->applyCameraState($cameraState);
    }

    // Feedback handlers
    #[On('reset-sound-flag')]
    public function resetSoundFlag(): void
    {
        // This will be handled by child components
    }

    #[On('reset-vibration-flag')]
    public function resetVibrationFlag(): void
    {
        // This will be handled by child components
    }

    /**
     * Apply camera state to component properties
     */
    private function applyCameraState(CameraState $cameraState): void
    {
        $stateArray = $cameraState->toArray();
        $this->applyStateArray($stateArray);
    }

    /**
     * Get current camera state from component properties
     */
    private function getCurrentCameraState(): CameraState
    {
        return new CameraState(
            isScanning: $this->isScanning,
            isLoading: $this->loadingCamera,
            torchSupported: $this->torchSupported,
            torchEnabled: $this->isTorchOn,
            error: $this->cameraError ?: null,
        );
    }

    /**
     * Reset state after scan submission
     */
    private function resetAfterScanSubmission(): void
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
    private function resetAfterEmptyBaySubmission(): void
    {
        $this->barcode = null;
        $this->barcodeScanned = false;
        $this->product = null;
        $this->showEmptyBayNotification = false;
        $this->cameraError = '';
        $this->resetValidation();
    }

    /**
     * Reset for new scan
     */
    private function resetForNewScan(): void
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
     * Apply an array of state changes to component properties
     */
    private function applyStateArray(array $stateArray): void
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

    /**
     * Handle auto-submit functionality
     *
     * This method automatically submits scans when auto-submit is enabled.
     * It creates a scan record with default values (quantity: 1, action: decrease)
     * and provides brief visual feedback before resetting for the next scan.
     */
    private function handleAutoSubmit(): void
    {
        if (! $this->product || $this->autoSubmitInProgress) {
            return;
        }

        $this->autoSubmitInProgress = true;

        try {
            // Use the auto-submit action to create the scan
            $result = $this->autoSubmitScanAction()->handle(
                product: $this->product,
                barcode: $this->barcode,
                userId: auth()->id()
            );

            if ($result['success']) {
                // Dispatch success notification to frontend
                $this->dispatch('auto-submit-success', [
                    'scan_id' => $result['scan_id'],
                    'product_name' => $this->product->name,
                ]);

                // Brief delay to show product info before resetting (handled by JavaScript)
                // After 1.5 seconds, JS will trigger reset and restart camera
                $this->dispatch('schedule-auto-submit-reset', [
                    'delay' => 1500, // milliseconds
                ]);
            } else {
                // Auto-submit failed - show error and don't reset
                $this->cameraError = $result['message'];
                $this->autoSubmitInProgress = false;
            }

        } catch (\Exception $e) {
            $this->cameraError = 'Auto-submit failed: '.$e->getMessage();
            $this->autoSubmitInProgress = false;
        }
    }

    /**
     * Reset after auto-submit (called by JavaScript after delay)
     */
    #[On('auto-submit-reset-complete')]
    public function onAutoSubmitResetComplete(): void
    {
        // Reset state and restart camera for next scan
        $resetState = $this->resetScanStateAction()->reset(ResetContext::AfterSubmission);
        $this->resetAfterScanSubmission();
        $this->applyStateArray($resetState);

        // Reset auto-submit flag
        $this->autoSubmitInProgress = false;

        // Dispatch camera start event
        if (isset($resetState['dispatchEvent'])) {
            $this->dispatch(...$resetState['dispatchEvent']);
        }
    }

    public function render()
    {
        return view('livewire.scanner.product-scanner');
    }
}
