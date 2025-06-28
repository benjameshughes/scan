<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class Scanner extends Component
{
    public array $result = [];
    public bool $isScanning = false;

    public bool $isTorchOn = false;

    public bool $loadingCamera = false;

    public bool $showVideo = false;

    public string $barcode;

    #[On('loadingCamera')]
    public function updateLoadingCamera(bool $loadingCamera)
    {
        $this->loadingCamera = !$loadingCamera;
    }

    #[On('camera')]
    public function camera()
    {
        $this->isScanning = ! $this->isScanning;
    }

    #[On('torch')]
    public function torchStatus()
    {
        $this->isTorchOn = ! $this->isTorchOn;
    }

    #[On('result')]
    public function updateBarcode(array $result)
    {
        $this->barcode = $result['text'];
        $this->dispatch('barcode', $this->barcode);
    }

    public function render()
    {
        return view('livewire.scanner');
    }
}
