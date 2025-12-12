<?php

namespace App\Livewire\Scanner;

use App\Actions\Scanner\LookupProductByBarcodeAction;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ProductInfo extends Component
{
    public ?int $productId = null;

    public ?string $barcode = null;

    public bool $barcodeScanned = false;

    public function mount(
        ?int $productId = null,
        ?string $barcode = null,
        bool $barcodeScanned = false,
    ) {
        $this->productId = $productId;
        $this->barcode = $barcode;
        $this->barcodeScanned = $barcodeScanned;
    }

    #[Computed]
    public function product(): ?Product
    {
        if ($this->productId) {
            return Product::find($this->productId);
        }

        // Fallback: lookup by barcode if no product ID
        if ($this->barcode) {
            return app(LookupProductByBarcodeAction::class)->handle($this->barcode);
        }

        return null;
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
