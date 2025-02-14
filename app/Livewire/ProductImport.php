<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Product;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class ProductImport extends Component
{
    use WithFileUploads;

    public $csvFile;
    public $headers = [];
    public $mapping = [];
    public $rows = [];
    public $step = 1; // step 1: upload, step 2: mapping, step 3: import

    // The fillable columns of the Product (except SKU is our key)
    public $modelColumns = ['sku', 'name', 'barcode', 'barcode_2', 'quantity'];

    public function render()
    {
        return view('livewire.product-import');
    }

    /**
     * Handle the file upload and get CSV headers and rows.
     */
    public function uploadFile()
    {
        $data = $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt',
        ]);

        // Load CSV and get data as collection
        $collection = Excel::toCollection(null, $this->csvFile->getRealPath());
        if ($collection->isEmpty() || $collection->first()->isEmpty()) {
            $this->addError('csvFile', 'CSV is empty or invalid.');
            return;
        }

        // Assume first row as header
        $this->headers = $collection->first()->first();
        // Save rows for later (skip header row)
        $this->rows = $collection->first()->slice(1)->toArray();
        $this->step = 2;
    }

    /**
     * Import the CSV rows after mapping.
     */
    public function import()
    {
        // Validate that mapping has keys for required fields
        $required = ['sku']; // always require sku for matching
        foreach ($required as $field) {
            if (!in_array($field, $this->mapping)) {
                $this->addError('mapping', "Mapping for {$field} is required.");
                return;
            }
        }

        // Loop through each row and build the data for upsert
        foreach ($this->rows as $rowIndex => $row) {
            $data = [];
            foreach ($this->mapping as $modelField) {
                // Get the CSV column index that maps to this model field.
                $csvIndex = array_search($modelField, $this->mapping);
                if ($csvIndex !== false && isset($row[$csvIndex])) {
                    $data[$modelField] = $row[$csvIndex];
                }
            }

            // Validate data if needed; here we do a simple check
            $validator = Validator::make($data, [
                'sku' => 'required',
            ]);

            if ($validator->fails()) {
                // Optional: Handle errors (log or accumulate errors to show later)
                continue;
            }

            // We upsert based on sku. The keys you want to update (only mapped values)
            Product::updateOrCreate(['sku' => $data['sku']], $data);
        }

        $this->step = 3;
    }
}
