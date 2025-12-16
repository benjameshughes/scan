<?php

namespace App\Livewire\Scanner\Concerns;

use App\DTOs\Scanner\CameraState;
use Livewire\Attributes\On;

/**
 * Manages camera state and related event handlers
 *
 * This trait handles all camera-related state properties and events including:
 * - Camera initialization, ready, and stopped states
 * - Torch support and state management
 * - Camera error handling
 * - Camera and torch toggle controls
 */
trait HasCameraState
{
    use InteractsWithScannerServices;

    // Camera state properties
    public bool $isScanning = false;

    public bool $isTorchOn = false;

    public bool $torchSupported = false;

    public bool $loadingCamera = false;

    public string $cameraError = '';

    /**
     * Handle camera initializing event
     */
    #[On('onCameraInitializing')]
    public function onCameraInitializing(): void
    {
        $cameraState = $this->cameraManagerService()->handleInitializing();
        $this->applyCameraState($cameraState);
    }

    /**
     * Handle camera ready event
     */
    #[On('onCameraReady')]
    public function onCameraReady(): void
    {
        $cameraState = $this->cameraManagerService()->handleReady();
        $this->applyCameraState($cameraState);
    }

    /**
     * Handle camera stopped event
     */
    #[On('onCameraStopped')]
    public function onCameraStopped(): void
    {
        // Update UI state when camera is stopped (e.g., app backgrounded)
        $this->isScanning = false;
        $this->loadingCamera = false;
        $this->isTorchOn = false;
    }

    /**
     * Handle camera error event
     */
    #[On('onCameraError')]
    public function onCameraError(string $error): void
    {
        $cameraState = $this->cameraManagerService()->handleError($error);
        $this->applyCameraState($cameraState);
    }

    /**
     * Handle torch support detection event
     */
    #[On('onTorchSupportDetected')]
    public function onTorchSupportDetected(bool $supported): void
    {
        $currentState = $this->getCurrentCameraState();
        $cameraState = $this->cameraManagerService()->updateTorchSupport($currentState, $supported);
        $this->applyCameraState($cameraState);
    }

    /**
     * Handle torch state changed event
     */
    #[On('onTorchStateChanged')]
    public function onTorchStateChanged(bool $enabled): void
    {
        $currentState = $this->getCurrentCameraState();
        $cameraState = $this->cameraManagerService()->updateTorchState($currentState, $enabled);
        $this->applyCameraState($cameraState);
    }

    /**
     * Handle camera toggle request
     */
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

    /**
     * Handle torch toggle request
     */
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

    /**
     * Handle error cleared event
     */
    #[On('error-cleared')]
    public function onErrorCleared(): void
    {
        $currentState = $this->getCurrentCameraState();
        $cameraState = $this->cameraManagerService()->clearError($currentState);
        $this->applyCameraState($cameraState);
    }

    /**
     * Apply camera state to component properties
     */
    protected function applyCameraState(CameraState $cameraState): void
    {
        $stateArray = $cameraState->toArray();
        $this->applyStateArray($stateArray);
    }

    /**
     * Get current camera state from component properties
     */
    protected function getCurrentCameraState(): CameraState
    {
        return new CameraState(
            isScanning: $this->isScanning,
            isLoading: $this->loadingCamera,
            torchSupported: $this->torchSupported,
            torchEnabled: $this->isTorchOn,
            error: $this->cameraError ?: null,
        );
    }
}
