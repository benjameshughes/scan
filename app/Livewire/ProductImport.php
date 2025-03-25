<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Product;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class ProductImport extends Component
{
    use WithFileUploads;

    public $csvFile;
    public $headers = [];
    public $mapping = [];
    public $rows = [];
    public $step = 1; // step 1: upload, step 2: mapping, step 3: import

    // The fillable columns of the Product
    public $modelColumns = [];

    public function render()
    {
        return view('livewire.product-import');
    }

    public function mount()
    {
        $this->modelColumns = (new Product())->getFillable();
    }

    /**
     * Handle the file upload and get CSV headers and rows.
     */
    public function uploadFile()
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt',
        ]);

        // Load CSV and get data as collection
        $collection = Excel::toCollection(null, $this->csvFile->getRealPath());
        if ($collection->isEmpty() || $collection->first()->isEmpty()) {
            $this->addError('csvFile', 'CSV is empty or invalid.');
            return;
        }

        // Assume first row as header
        $this->headers = $collection->first()->first()->toArray();

        // Save rows for later (skip header row)
        $this->rows = $collection->first()->slice(1)->toArray();

        // Initialize mapping with empty values
        $this->mapping = array_fill_keys($this->modelColumns, '');

        // Try to auto-map columns based on header names
        foreach ($this->headers as $index => $header) {
            $normalizedHeader = strtolower(trim($header));
            if (in_array($normalizedHeader, $this->modelColumns)) {
                $this->mapping[$normalizedHeader] = $index;
            }
        }

        $this->step = 2;
    }

    /**
     * Import the CSV rows after mapping.
     */
    public function import()
    {
        // Validate that mapping has keys for required fields
        if (!isset($this->mapping['sku']) || $this->mapping['sku'] === '') {
            $this->addError('mapping', 'Mapping for SKU is required.');
            return;
        }

        $importCount = 0;
        $errorCount = 0;

        // Loop through each row and build the data for upsert
        foreach ($this->rows as $rowIndex => $row) {
            $data = [];

            // Extract data based on mapping
            foreach ($this->mapping as $modelField => $headerIndex) {
                if ($headerIndex !== '' && isset($row[$headerIndex])) {
                    $data[$modelField] = $row[$headerIndex];
                }
            }

            // Validate data
            $validator = Validator::make($data, [
                'sku' => 'required',
            ]);

            if ($validator->fails()) {
                $errorCount++;
                continue;
            }

            // Upsert based on sku
            try {
                Product::updateOrCreate(['sku' => $data['sku']], $data);
                $importCount++;
            } catch (\Exception $e) {
                $errorCount++;
                // You might want to log the error or handle it differently
            }
        }

        session()->flash('message', "Import completed: $importCount products imported, $errorCount errors.");
        $this->step = 3;
    }
}
