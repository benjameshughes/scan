<?php

namespace App\Livewire\Scanner;

use App\Models\Product;
use Livewire\Component;

class ProductInfo extends Component
{
    public ?Product $product = null;

    public ?string $barcode = null;

    public bool $barcodeScanned = false;

    public function mount(
        ?Product $product = null,
        ?string $barcode = null,
        bool $barcodeScanned = false,
    ) {
        $this->product = $product;
        $this->barcode = $barcode;
        $this->barcodeScanned = $barcodeScanned;
    }

    public function startNewScan()
    {
        $this->dispatch('new-scan-requested');
    }

    public function render()
    {
        return view('livewire.scanner.product-info');
    }
}
