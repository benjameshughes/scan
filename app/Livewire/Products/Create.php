<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Livewire\Component;

class Create extends Component
{
    public string $sku = '';

    public string $name = '';

    public string $barcode = '';

    public string $barcode_2 = '';

    public string $barcode_3 = '';

    public int $quantity = 0;

    protected $rules = [
        'sku' => 'required|string|max:255|unique:products,sku',
        'name' => 'required|string|max:255',
        'barcode' => 'required|string|max:255|unique:products,barcode',
        'barcode_2' => 'nullable|string|max:255|unique:products,barcode_2',
        'barcode_3' => 'nullable|string|max:255|unique:products,barcode_3',
        'quantity' => 'required|integer|min:0',
    ];

    protected $messages = [
        'sku.required' => 'SKU is required.',
        'sku.unique' => 'This SKU already exists.',
        'name.required' => 'Product name is required.',
        'barcode.required' => 'Primary barcode is required.',
        'barcode.unique' => 'This barcode already exists.',
        'barcode_2.unique' => 'This secondary barcode already exists.',
        'barcode_3.unique' => 'This tertiary barcode already exists.',
        'quantity.required' => 'Quantity is required.',
        'quantity.min' => 'Quantity cannot be negative.',
    ];

    public function save()
    {
        $this->validate();

        // Remove empty barcode fields
        $data = [
            'sku' => $this->sku,
            'name' => $this->name,
            'barcode' => $this->barcode,
            'quantity' => $this->quantity,
        ];

        if (! empty($this->barcode_2)) {
            $data['barcode_2'] = $this->barcode_2;
        }

        if (! empty($this->barcode_3)) {
            $data['barcode_3'] = $this->barcode_3;
        }

        Product::create($data);

        session()->flash('message', 'Product created successfully!');

        $this->redirect(route('products.index'));
    }

    public function render()
    {
        return view('livewire.products.create');
    }
}
