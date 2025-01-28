<?php

namespace App\Livewire;

use App\DataTransferObjects\ScanDTO;
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
    public string $barcode;

    #[Validate('required|min:1')]
    public int $quantity = 1;

    public int $productId = 0;

    public bool $showSuccessMessage = false;

    public bool $barcodeScanned = false;

    #[On('barcode')]
    public function updateBarcode($barcode)
    {
        $this->barcode = $barcode;
        $this->dispatch('stop-scan');
        $this->barcodeScanned = true;
    }

    public function checkBarcodeExists(): bool
    {
        // Find the sku for the barcode
        $product = Product::where('barcode', $this->barcode)->first();

        if (!$product) {
            $users = User::all();
            foreach ($users as $user) {
                $user->notify(new NoSkuFound($this->barcode));
            }
        }

        return true;
    }

    // Save function
    public function save()
    {

        $this->showSuccessMessage = true;

        $this->validate();

        // New Scan DTO
        $scanDTO = new ScanDTO(
            $this->barcode,
            $this->quantity,
            'false',
            now()->toDateTimeString(),
            now()->toDateTimeString()
        );

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

        // Page refresh because I can't figure out how to start and stop the scanner view without refreshing in the js file
        return redirect()->route('scan.scan');
    }

    public function render()
    {
        return view('livewire.scan-form');
    }
}
