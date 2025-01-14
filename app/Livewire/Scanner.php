<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class Scanner extends Component
{
    public array $result = [];
    public bool $isScanning = false;

    public bool $flash = false;

    public bool $loadingCamera = false;

    public string $barcode;

    #[On('loadingCamera')]
    public function updateLoadingCamera(bool $loadingCamera)
    {
        $this->loadingCamera = $loadingCamera;
    }

    #[On('newScan')]
    public function startScan()
    {
        $this->isScanning = true;
        $this->dispatch('startScan');
    }

    #[On('stopScan')]
    public function stopScan()
    {
        $this->isScanning = false;
//        $this->dispatch('stopScan');
    }

    #[On('flashOn')]
    public function flash()
    {
        $this->flash = true;
        $this->dispatch('flashOn');
    }

    #[On('flashOff')]
    public function flashOff()
    {
        $this->flash = false;
        $this->dispatch('flashOff');
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
