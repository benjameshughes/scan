<?php

namespace App\Services\Scanner;

use App\DTOs\Scanner\CameraState;
use Illuminate\Support\Facades\Log;

class CameraManagerService
{
    /**
     * Initialize camera state for component mount
     */
    public function getInitialState(): CameraState
    {
        return new CameraState(
            isScanning: false,
            isLoading: false, // Start in "Ready to Scan" state
            torchSupported: false,
            torchEnabled: false,
        );
    }

    /**
     * Handle camera initialization callback from JavaScript
     */
    public function handleInitializing(): CameraState
    {
        Log::debug('Camera initialization started');

        return new CameraState(
            isScanning: false,
            isLoading: true,
            torchSupported: false,
            torchEnabled: false,
        );
    }

    /**
     * Handle camera ready callback from JavaScript
     */
    public function handleReady(): CameraState
    {
        Log::debug('Camera ready and scanning');

        return new CameraState(
            isScanning: true,
            isLoading: false,
            torchSupported: false, // Will be updated by torch support detection
            torchEnabled: false,
        );
    }

    /**
     * Handle camera error callback from JavaScript
     */
    public function handleError(string $error): CameraState
    {
        Log::warning('Camera error occurred', ['error' => $error]);

        return new CameraState(
            isScanning: false,
            isLoading: false,
            torchSupported: false,
            torchEnabled: false,
            error: $error,
        );
    }

    /**
     * Handle camera toggle (start/stop)
     */
    public function handleToggle(CameraState $currentState): array
    {
        if ($currentState->isScanning) {
            // Stopping camera - direct toggle
            Log::debug('Stopping camera via toggle');

            return [
                'state' => new CameraState(
                    isScanning: false,
                    isLoading: false,
                    torchSupported: $currentState->torchSupported,
                    torchEnabled: false, // Turn off torch when stopping
                ),
                'dispatchEvent' => ['camera-state-changed', false],
            ];
        } else {
            // Starting camera - use loading flow
            Log::debug('Starting camera via toggle');

            return [
                'state' => new CameraState(
                    isScanning: false, // Will be set to true by onCameraReady()
                    isLoading: true,
                    torchSupported: $currentState->torchSupported,
                    torchEnabled: false,
                ),
                'dispatchEvent' => ['camera-state-changed', true],
            ];
        }
    }

    /**
     * Handle torch toggle
     */
    public function handleTorchToggle(CameraState $currentState): array
    {
        if (! $currentState->torchSupported) {
            return [
                'state' => $currentState,
                'error' => 'Torch not supported on this device',
            ];
        }

        $newTorchState = ! $currentState->torchEnabled;

        Log::debug('Toggling torch', ['enabled' => $newTorchState]);

        return [
            'state' => new CameraState(
                isScanning: $currentState->isScanning,
                isLoading: $currentState->isLoading,
                torchSupported: $currentState->torchSupported,
                torchEnabled: $newTorchState,
                error: $currentState->error,
            ),
            'dispatchEvent' => ['torch-state-changed', $newTorchState],
        ];
    }

    /**
     * Update torch support detection
     */
    public function updateTorchSupport(CameraState $currentState, bool $supported): CameraState
    {
        Log::debug('Torch support detected', ['supported' => $supported]);

        return new CameraState(
            isScanning: $currentState->isScanning,
            isLoading: $currentState->isLoading,
            torchSupported: $supported,
            torchEnabled: $supported ? $currentState->torchEnabled : false,
            error: $currentState->error,
        );
    }

    /**
     * Update torch state from JavaScript callback
     */
    public function updateTorchState(CameraState $currentState, bool $enabled): CameraState
    {
        Log::debug('Torch state updated from JavaScript', ['enabled' => $enabled]);

        return new CameraState(
            isScanning: $currentState->isScanning,
            isLoading: $currentState->isLoading,
            torchSupported: $currentState->torchSupported,
            torchEnabled: $enabled,
            error: $currentState->error,
        );
    }

    /**
     * Reset camera state (used during scan reset)
     */
    public function resetState(): CameraState
    {
        Log::debug('Resetting camera state');

        return new CameraState(
            isScanning: false,
            isLoading: false,
            torchSupported: false,
            torchEnabled: false,
        );
    }

    /**
     * Prepare camera for new scan (after form submission)
     */
    public function prepareForNewScan(): array
    {
        Log::debug('Preparing camera for new scan');

        return [
            'state' => new CameraState(
                isScanning: false, // Will be set to true by onCameraReady()
                isLoading: true,
                torchSupported: false, // Will be updated when camera initializes
                torchEnabled: false,
            ),
            'dispatchEvent' => ['camera-state-changed', true],
        ];
    }

    /**
     * Clear camera error
     */
    public function clearError(CameraState $currentState): CameraState
    {
        return new CameraState(
            isScanning: $currentState->isScanning,
            isLoading: $currentState->isLoading,
            torchSupported: $currentState->torchSupported,
            torchEnabled: $currentState->torchEnabled,
            error: null,
        );
    }
}
