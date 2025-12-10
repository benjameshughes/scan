<?php

namespace App\Livewire\Scanner;

use Livewire\Component;

class CameraDisplay extends Component
{
    public bool $isScanning = false;

    public bool $loadingCamera = false;

    public bool $torchSupported = false;

    public bool $isTorchOn = false;

    public string $cameraError = '';

    public bool $barcodeScanned = false;

    public function mount(
        bool $isScanning = false,
        bool $loadingCamera = false,
        bool $torchSupported = false,
        bool $isTorchOn = false,
        string $cameraError = '',
        bool $barcodeScanned = false,
    ) {
        $this->isScanning = $isScanning;
        $this->loadingCamera = $loadingCamera;
        $this->torchSupported = $torchSupported;
        $this->isTorchOn = $isTorchOn;
        $this->cameraError = $cameraError;
        $this->barcodeScanned = $barcodeScanned;
    }

    public function toggleCamera()
    {
        $this->dispatch('camera-toggle-requested');
    }

    public function toggleTorch()
    {
        $this->dispatch('torch-toggle-requested');
    }

    public function clearError()
    {
        $this->dispatch('error-cleared');
    }

    public function render()
    {
        return view('livewire.scanner.camera-display');
    }
}
