<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Livewire\Component;

class Edit extends Component
{
    public Product $product;

    public string $sku = '';

    public string $name = '';

    public string $barcode = '';

    public string $barcode_2 = '';

    public string $barcode_3 = '';

    public int $quantity = 0;

    public function mount(Product $product)
    {
        $this->product = $product;
        $this->sku = $product->sku;
        $this->name = $product->name ?? '';
        $this->barcode = $product->barcode ?? '';
        $this->barcode_2 = $product->barcode_2 ?? '';
        $this->barcode_3 = $product->barcode_3 ?? '';
        $this->quantity = $product->quantity ?? 0;
    }

    protected function rules()
    {
        return [
            'sku' => 'required|string|max:255|unique:products,sku,'.$this->product->id,
            'name' => 'required|string|max:255',
            'barcode' => 'required|string|max:255|unique:products,barcode,'.$this->product->id,
            'barcode_2' => 'nullable|string|max:255|unique:products,barcode_2,'.$this->product->id,
            'barcode_3' => 'nullable|string|max:255|unique:products,barcode_3,'.$this->product->id,
            'quantity' => 'required|integer|min:0',
        ];
    }

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

        // Prepare data for update
        $data = [
            'sku' => $this->sku,
            'name' => $this->name,
            'barcode' => $this->barcode,
            'quantity' => $this->quantity,
        ];

        // Handle optional barcode fields
        $data['barcode_2'] = ! empty($this->barcode_2) ? $this->barcode_2 : null;
        $data['barcode_3'] = ! empty($this->barcode_3) ? $this->barcode_3 : null;

        $this->product->update($data);

        session()->flash('message', 'Product updated successfully!');

        $this->redirect(route('products.show', $this->product));
    }

    public function render()
    {
        return view('livewire.products.edit');
    }
}
