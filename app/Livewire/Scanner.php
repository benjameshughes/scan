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

    public function mount()
    {
        // Ensure user is authenticated and has scanner permission
        if (! auth()->check()) {
            abort(401, 'Authentication required');
        }

        if (! auth()->user()->can('view scanner')) {
            abort(403, 'Insufficient permissions to use scanner');
        }
    }

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
            $this->isScanning = ! $this->isScanning;
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
        $this->isTorchOn = (bool) $enabled;
    }

    #[On('torchStatusUpdated')]
    public function torchStatusUpdated($enabled, $supported)
    {
        $this->isTorchOn = (bool) $enabled;
        $this->torchSupported = (bool) $supported;

        if (! $supported) {
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
