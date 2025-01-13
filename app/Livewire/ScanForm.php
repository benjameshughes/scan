<?php

namespace App\Livewire;

use App\Jobs\SyncBarcode;
use App\Models\Scan;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class ScanForm extends Component
{
    public Scan $scan;

    public string $barcode;
    public int $quantity;

    // Define the validation rules
    protected $rules = [
        'barcode' => 'required|string',
        'quantity' => 'required|numeric|min:1', // Add quantity validation
    ];

    #[On('barcode')]
    public function updatedBarcode($barcode)
    {
        $this->barcode = $barcode;
        $this->quantity = 1;
        $this->save();
    }

    // Save function
    public function save()
    {
        $this->validate(); // Use the rules defined above for validation

        // Save the data to the database
        Scan::create([
            'barcode' => $this->barcode,
            'quantity' => $this->quantity,
            'submitted' => false,
            'user_id' => auth()->id(),
        ]);

        // Put the scan into an array
        $scan = [
            'barcode' => $this->barcode,
            'quantity' => $this->quantity,
            'submitted' => false,
            'user_id' => auth()->id(),
        ];

        Log::channel('barcode')->info("{$this->barcode} Scanned");

        // Flash success message
        session()->flash('success', 'Scan Saved Successfully');

        // Stop the scanner
        $this->dispatch('stopScan');

        // Dispatch the sync job
        SyncBarcode::dispatch($scan)->delay(now()->addMinutes(1));

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
