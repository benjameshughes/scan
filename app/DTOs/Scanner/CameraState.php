<?php

namespace App\DTOs\Scanner;

readonly class CameraState
{
    public function __construct(
        public bool $isScanning,
        public bool $isLoading,
        public bool $torchSupported,
        public bool $torchEnabled,
        public ?string $error = null,
    ) {}

    /**
     * Check if camera is ready for scanning
     */
    public function isReady(): bool
    {
        return $this->isScanning && ! $this->isLoading && ! $this->error;
    }

    /**
     * Check if camera is in an error state
     */
    public function hasError(): bool
    {
        return ! empty($this->error);
    }

    /**
     * Get display status for UI
     */
    public function getDisplayStatus(): string
    {
        if ($this->hasError()) {
            return 'Error';
        }

        if ($this->isLoading) {
            return 'Loading';
        }

        if ($this->isScanning) {
            return 'Scanning';
        }

        return 'Stopped';
    }

    /**
     * Convert to array for Livewire component properties
     */
    public function toArray(): array
    {
        return [
            'isScanning' => $this->isScanning,
            'loadingCamera' => $this->isLoading,
            'torchSupported' => $this->torchSupported,
            'isTorchOn' => $this->torchEnabled,
            'cameraError' => $this->error ?? '',
        ];
    }
}
