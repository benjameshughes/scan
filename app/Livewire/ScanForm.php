<?php

namespace App\Livewire;

use App\Actions\GetProductFromScannedBarcode;
use App\DTOs\EmptyBayDTO;
use App\Jobs\EmptyBayJob;
use App\Jobs\SyncBarcode;
use App\Models\Scan;
use App\Rules\BarcodePrefixCheck;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ScanForm extends Component
{
    public Scan $scan;

    #[Validate(['required', new BarcodePrefixCheck('505903')])]
    public ?int $barcode = null;

    #[Validate('required|min:1')]
    public int $quantity = 1;

    public bool $barcodeScanned = false;

    public bool $showSuccessMessage = false;

    public string $successMessage;

    public bool $scanAction = false;

    public function incrementQuantity(): int
    {
        return $this->quantity++;
    }

    public function updatedScanAction()
    {
        // Remove the toggle logic - wire:model.live handles the update automatically
        // The previous code was causing an infinite loop
    }

    #[On('barcode')]
    public function updatedBarcode($barcode)
    {
        $this->barcode = $barcode;
        $this->barcodeScanned = true;

        if ($this->validate()) {
            $product = (new GetProductFromScannedBarcode($this->barcode))->handle();
            $this->successMessage = $product ? $product->name : 'No Product Found With That Barcode';
            $this->showSuccessMessage = true;
        }

        $this->dispatch('stop-scan');
    }

    public function emptyBayNotification()
    {
        // Create a DTO
        $emptyBayDTO = new EmptyBayDTO(
            $this->barcode,
        );

        // Pass DTO to notification
        EmptyBayJob::dispatch($emptyBayDTO);

        $this->showSuccessMessage = true;
        $this->successMessage = 'Empty bay notification sent';
    }

    // Save function
    public function save()
    {
        $this->validate();

        // Create a new ScanDTO
        $this->scan = Scan::create([
            'barcode' => $this->barcode,
            'quantity' => $this->quantity,
            'submitted' => false,
            'scanAction' => $this->scanAction,
            'sync_status' => 'pending',
            'user_id' => auth()->check() ? auth()->user()->id : '1',
        ]);

        // Dispatch the sync job
        SyncBarcode::dispatch($this->scan);

        Log::channel('barcode')->info("{$this->barcode} Scanned");

        redirect()->route('home');
    }

    public function render()
    {
        return view('livewire.scan-form');
    }
}
