<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class Scanner extends Component
{
    public array $result = [];
    public bool $isScanning = false;

    public bool $torch = false;

    public bool $loadingCamera = false;

    public bool $showVideo = false;

    public string $barcode;

    #[On('loadingCamera')]
    public function updateLoadingCamera(bool $loadingCamera)
    {
        $this->loadingCamera = $loadingCamera;
    }

    #[On('startScan')]
    public function startScan()
    {
        $this->isScanning = true;
    }

    #[On('stopScan')]
    public function stopScan()
    {
        $this->isScanning = false;
    }

    #[On('torchOn')]
    public function torchOn()
    {
        $this->torch = true;
    }

    #[On('torchOff')]
    public function torchOff()
    {
        $this->torch = false;
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
