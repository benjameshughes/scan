<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Livewire\Component;

class Show extends Component
{
    public Product $product;

    public function mount(Product $product)
    {
        $this->product = $product;
    }

    public function render()
    {
        // Get recent scans for this product
        $recentScans = $this->product->scans()
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();

        return view('livewire.products.show', [
            'recentScans' => $recentScans,
        ]);
    }
}
