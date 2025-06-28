<?php

namespace App\Livewire;

use App\Enums\ImportTypes;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Product;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\HeadingRowImport;

class ProductImport extends Component
{
    use WithFileUploads;

    public $csvFile;
    public $headers = [];
    public $mapping = [];
    public $rows = [];
    public int $importCount = 0;
    public int $errorCount = 0;
    public array $errors = [];
    public $step = 1; // step 1: upload, step 2: mapping, step 3: import
    public $modelColumns = [];
    public array $importTypes = [];
    public string $importAction = 'create';
    public $previewRows = [];
    public $totalRows = 0;

    public function mount()
    {
        $this->modelColumns = new Product()->getFillable();
        $this->mapping = array_fill_keys($this->modelColumns, '');
        $this->importTypes = ImportTypes::toArray();
    }

    /**
     * Handle the file upload and get CSV headers and rows.
     */
    public function uploadFile()
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,xlsx|max:10240',
        ]);

        try {
            // Load CSV and get data as collection
            $collection = Excel::toCollection((object)null, $this->csvFile->getRealPath());

            if ($collection->isEmpty() || $collection->first()->isEmpty()) {
                $this->addError('csvFile', 'CSV is empty or invalid.');
                return;
            }

            $firstSheet = $collection->first();

            // Assume first row as header
            $this->headers = new HeadingRowImport()->toCollection($this->csvFile->getRealPath());

            // Save rows for later (skip header row)
            $this->rows = $firstSheet->slice(1);
            $this->totalRows = count($this->rows);

            // Get a preview of the first 5 rows for display
            $this->previewRows = $firstSheet->slice(1,5)->mapWithKeys()->each(function ($row) {
                $this->previewRows[] = $row;
            });

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
        } catch (\Exception $e) {
            Log::error('CSV upload error: ' . $e->getMessage());
            $this->addError('csvFile', 'Failed to process the file: ' . $e->getMessage());
        }
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

        $this->importCount = 0;
        $this->errorCount = 0;
        $this->errors = [];

        // Loop through each row and build the data for import
        foreach ($this->rows as $rowIndex => $row) {
            $data = [];

            // Extract data based on mapping
            foreach ($this->mapping as $modelField => $headerIndex) {
                if ($headerIndex !== '' && isset($row[$headerIndex])) {
                    $data[$modelField] = $row[$headerIndex];
                }
            }

            // Validate data
            try {
                $validator = Validator::make($data, [
                    'sku' => 'required|string|max:255',
                    // Add other validation rules as needed
                ]);

                if ($validator->fails()) {
                    $this->errorCount++;
                    $this->errors[] = [
                        'row' => $rowIndex + 2, // +2 because of 0-index and header row
                        'message' => 'Validation failed: ' . implode(', ', $validator->errors()->all()),
                        'data' => $data
                    ];
                    continue;
                }

                // Handle the action (create, update, or delete)
                switch ($this->importAction) {
                    case 'create':
                        Product::create($data);
                        break;
                    case 'update':
                        $product = Product::where('sku', $data['sku'])->first();
                        if ($product) {
                            $product->update($data);
                        } else {
                            throw new \Exception("Product with SKU {$data['sku']} not found");
                        }
                        break;
                    case 'delete':
                        $product = Product::where('sku', $data['sku'])->first();
                        if ($product) {
                            $product->delete();
                        } else {
                            throw new \Exception("Product with SKU {$data['sku']} not found");
                        }
                        break;
                    default:
                        throw new \Exception("Invalid import action: {$this->importAction}");
                }

                $this->importCount++;
            } catch (\Exception $e) {
                $this->errorCount++;
                $this->errors[] = [
                    'row' => $rowIndex + 2,
                    'message' => $e->getMessage(),
                    'data' => $data
                ];
                Log::error("Import error at row " . ($rowIndex + 2) . ": " . $e->getMessage());
            }
        }

        $this->step = 3;
    }
}
