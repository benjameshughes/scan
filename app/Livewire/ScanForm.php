<?php

namespace App\Livewire;

use App\DTOs\ScanDTO;
use App\Jobs\SyncBarcode;
use App\Models\Product;
use App\Models\Scan;
use App\Models\User;
use App\Notifications\NoSkuFound;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

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

    public function checkBarcodeExists(): bool
    {
        // Find the sku for the barcode
        $product = Product::where('barcode', $this->barcode)->first();

        if (!$product) {
            return false;
        }

        return true;
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
        if ($this->checkBarcodeExists()) {
            SyncBarcode::dispatch($scan->id)->delay(now()->addMinute());
        } else {
            $users = User::all();
            foreach($users as $user) {
                $user->notify(new NoSkuFound($scan->id));
            }
            // Update scan status to failed
            $scan->update(['status' => 'failed']);
        }

        Log::channel('barcode')->info("{$this->barcode} Scanned");

        redirect()->route('home');
    }

    public function render()
    {
        return view('livewire.scan-form');
    }
}
