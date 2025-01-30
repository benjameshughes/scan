<?php

namespace App\Livewire;

use App\Imports\ProductsImport;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ProductImport extends Component
{
    use WithFileUploads;

    public $file;
    public array $fileColumns = [];
    public array $modelColumns = [];
    public array $mappings = [];
    public bool $showMappingError = false;
    public array $errors = [];

    public function mount()
    {
        // Set the model columns array to the fillable items of the product model
        $this->modelColumns = ((new Product())->getFillable());
    }

    /**
     * When a user uploads a spreadsheet, read the contents and get the headers from the first row.
     * Then push the headers to the fileColumns array.
     * @return void
     */
    public function updatedFile()
    {
        // Validate the file
        $this->validate([
            'file' => 'required|file|mimes:csv|max:10240',
        ]);
        // Store the file in the temp directory
        $this->file->storeAs('imports', $this->file->getClientOriginalName());
        // Read the sheet to get the column names
        $data = Excel::toArray(new ProductsImport($this->mappings), $this->file->getRealPath())[0];
        // Push the column names to the fileColumns array
        $this->fileColumns = array_keys($data[0]);
    }

    /**
     * Update the mappings array to associate the file columns with the model columns when a user chooses a file column from the dropdown
     * @return void
     */
    public function updateMappings()
    {
        // Loop over the model columns and set the value to the file column
        foreach ($this->modelColumns as $modelColumn) {
            $this->mappings[$modelColumn] = $this->fileColumns[$modelColumn];
        }
    }

    /**
     * Import the data using the mappings array to Laravel Excel model import
     */
    public function importData()
    {
        // Check if SKU mapping is missing
        if (!in_array('sku', $this->fileColumns)) {
            $this->showMappingError = true;
        }

        // Retrieve the file from the temp directory
        $path = $this->file->getRealPath();

        try {
            // Import the data using the mappings array
            $import = new ProductsImport($this->mappings);
            $import->import($path);

            session()->flash('message', 'Products imported');
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }



    public function render()
    {
        return view('livewire.product-import');
    }
}
