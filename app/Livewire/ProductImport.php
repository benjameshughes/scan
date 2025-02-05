<?php

namespace App\Livewire;

use App\Imports\ProductsImport;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

class ProductImport extends Component
{
    use WithFileUploads;

    public $file;
    public $mappings = [];
    public $availableColumns = [];

    public $fileColumns = [];
    public $results = [];

    public function updatedFile()
    {
        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        // Read file contents using php to get the columns name
        $headers = (new HeadingRowImport)->toArray($this->file)[0][0] ?? [];

        // Set headers to the file columns
        $this->fileColumns = $headers;
    }

    public function import()
    {
        $this->validate([
            'file' => 'required',
            'mappings' => 'required|array',
            'mappings.sku' => 'required|string', // SKU mapping is required
        ]);

        try {
            $import = new ProductsImport($this->mappings);
            $import->import($this->file);

            $this->results = $import->getResults();

            $this->dispatch('import-complete', [
                'message' => "Import completed: {$this->results['created']} created, {$this->results['updated']} updated, {$this->results['failed']} failed"
            ]);
        } catch (\Exception $e) {
            $this->addError('import', 'Import failed: ' . $e->getMessage());
        }
    }

    public function mount()
    {
        // Get the fillables from the model
        $this->availableColumns = (new Product())->getFillable();
    }

    public function render()
    {
        return view('livewire.product-import');
    }
}