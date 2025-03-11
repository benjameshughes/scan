<?php

namespace App\Livewire;

use App\Jobs\SyncBarcode;
use App\Models\Product;
use App\Models\Scan;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use PhpParser\Node\Expr\Ternary;

class ScanForm extends Component
{
    public Scan $scan;

    #[Validate('required')]
    public int $barcode;

    #[Validate('required|min:1')]
    public int $quantity = 1;

    public int $productId;

    public bool $barcodeScanned = false;

    public bool $showSuccessMessage = false;

    public function incrementQuantity()
    {
        $this->quantity++;
    }

    #[On('barcode')]
    public function updatedBarcode($barcode)
    {
        $this->barcode = $barcode;
        $this->barcodeScanned = true;
        $this->dispatch('stop-scan');
    }

    // Save function
    public function save()
    {
        $this->showSuccessMessage = true;

        $this->validate();

        // Create a new ScanDTO
        $scan = Scan::create([
            'barcode' => $this->barcode,
            'quantity' => $this->quantity,
            'submitted' => false,
            'sync_status' => 'pending',
            'user_id' => auth()->check() ? auth()->user()->id : '1',
        ]);

        // Dispatch the sync job
        SyncBarcode::dispatch($scan);

        Log::channel('barcode')->info("{$this->barcode} Scanned");

        redirect()->route('home');
    }

    public function render()
    {
        return view('livewire.scan-form');
    }
}
