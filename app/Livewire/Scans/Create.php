<?php

namespace App\Livewire\Scans;

use App\Jobs\SyncBarcode;
use App\Models\Product;
use App\Models\Scan;
use Livewire\Component;

class Create extends Component
{
    public string $barcode = '';

    public int $quantity = 1;

    public string $action = 'decrease';

    public ?Product $selectedProduct = null;

    protected $rules = [
        'barcode' => 'required|string|max:255',
        'quantity' => 'required|integer|min:1',
        'action' => 'required|in:increase,decrease',
    ];

    protected $messages = [
        'barcode.required' => 'Barcode is required.',
        'quantity.required' => 'Quantity is required.',
        'quantity.min' => 'Quantity must be at least 1.',
        'action.required' => 'Action is required.',
        'action.in' => 'Action must be either increase or decrease.',
    ];

    public function updatedBarcode()
    {
        if (strlen($this->barcode) >= 3) {
            $this->selectedProduct = Product::where('barcode', $this->barcode)
                ->orWhere('barcode_2', $this->barcode)
                ->orWhere('barcode_3', $this->barcode)
                ->first();
        } else {
            $this->selectedProduct = null;
        }
    }

    public function save()
    {
        $this->validate();

        // Check if product exists
        $product = Product::where('barcode', $this->barcode)
            ->orWhere('barcode_2', $this->barcode)
            ->orWhere('barcode_3', $this->barcode)
            ->first();

        if (! $product) {
            $this->addError('barcode', 'No product found with this barcode.');

            return;
        }

        // Store positive quantity - let the action field determine the operation
        $scan = Scan::create([
            'barcode' => $this->barcode,
            'quantity' => $this->quantity, // Always store positive value
            'action' => $this->action,
            'user_id' => auth()->id(),
            'submitted' => false,
            'sync_status' => 'pending',
        ]);

        // Dispatch sync job
        SyncBarcode::dispatch($scan);

        session()->flash('message', 'Scan recorded and queued for sync successfully!');

        // Reset form
        $this->reset(['barcode', 'quantity']);
        $this->action = 'decrease';
        $this->selectedProduct = null;
    }

    public function render()
    {
        return view('livewire.scans.create');
    }
}
