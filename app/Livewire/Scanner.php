<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class Scanner extends Component
{
    public array $result = [];
    public bool $isScanning = false;
    public bool $isTorchOn = false;
    public bool $torchSupported = false;
    public bool $loadingCamera = false;
    public string $cameraError = '';
    public string $barcode = '';

    #[On('loadingCamera')]
    public function updateLoadingCamera($loading)
    {
        $this->loadingCamera = $loading;
    }

    #[On('camera')]
    public function camera($isScanning = null)
    {
        if ($isScanning !== null) {
            $this->isScanning = $isScanning;
        } else {
            $this->isScanning = !$this->isScanning;
        }
    }

    #[On('torch')]
    public function torchStatus()
    {
        // Let JavaScript handle the actual torch toggle
        // This just triggers the JS event
    }

    #[On('torchStatus')]
    public function updateTorchStatus($enabled)
    {
        $this->isTorchOn = $enabled;
    }

    #[On('torchStatusUpdated')]
    public function torchStatusUpdated($enabled, $supported)
    {
        $this->isTorchOn = $enabled;
        $this->torchSupported = $supported;
        
        if (!$supported) {
            $this->cameraError = 'Torch not supported on this device';
        }
    }

    #[On('result')]
    public function updateBarcode(array $result)
    {
        $this->barcode = $result['text'];
        $this->dispatch('barcode', $this->barcode);
    }

    #[On('barcodeScanned')]
    public function barcodeScanned()
    {
        // Handle barcode scanned event
    }

    public function clearError()
    {
        $this->cameraError = '';
    }

    public function render()
    {
        return view('livewire.scanner');
    }
}
