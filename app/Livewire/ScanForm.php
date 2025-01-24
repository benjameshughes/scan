<?php

namespace App\Livewire;

use App\Jobs\SyncBarcode;
use App\Models\Product;
use App\Models\Scan;
use App\Notifications\NoSkuFound;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ScanForm extends Component
{
    public Scan $scan;

    #[Validate('required')]
    public string $barcode;

    #[Validate('required|min:1')]
    public int $quantity = 1;

    public int $productId = 0;

    public bool $showSuccessMessage = false;

    #[On('barcode')]
    public function updateBarcode($barcode)
    {
        $this->barcode = $barcode;
        $this->dispatch('stop-scan');
    }

    public function checkBarcodeExists()
    {
        // Find the sku for the barcode
        $product = Product::where('barcode', $this->barcode)->first();

        if (!$product) {
            auth()->user()->notify(new NoSkuFound($this->barcode));
            return false;
        }

        return true;
    }

    // Save function
    public function save()
    {

        $this->showSuccessMessage = true;

        $this->validate();

        // Save the data to the database
        $scan = Scan::create([
            'barcode' => $this->barcode,
            'quantity' => $this->quantity,
            'submitted' => false,
            'user_id' => auth()->check() ? auth()->id() : '1',
        ]);

        // Dispatch the sync job
        if ($this->checkBarcodeExists()) {
            SyncBarcode::dispatch($scan->id, $this->productId)->delay(now()->addMinute());
        } else {
            $this->addError('barcode', 'Barcode not recognised');
        }

        Log::channel('barcode')->info("{$this->barcode} Scanned");

        // Stop the scanner
        $this->dispatch('stopScan');

        // Reset the form
        $this->reset(['barcode', 'quantity']);



        // Optionally redirect (with flash message)
        return redirect()->route('scan.create');
    }

    public function render()
    {
        return view('livewire.scan-form');
    }
}
