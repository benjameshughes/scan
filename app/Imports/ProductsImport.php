<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpsertColumns;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToModel, WithHeadingRow, WithBatchInserts, WithValidation, SkipsOnError, WithUpserts, WithUpsertColumns
{
    use Importable, SkipsErrors;

    protected $mappings;

    public function __construct(array $mappings)
    {
        $this->mappings = $mappings;
    }

    /**
     * Pass the mappings array from the Livewire component to the model to import the data
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row): ?Product
    {
        // Initialize the data array with only the mapped columns
        $productData = [];

        // Iterate over the mappings and map the file columns to the model columns
        foreach ($this->mappings as $modelColumn => $fileColumn) {
            // Check if the file column exists in the current row
            if (isset($row[$fileColumn])) {
                // If it exists, add the mapped model column to the productData array
                $productData[$modelColumn] = $row[$fileColumn];
            }
        }

        // If there's no data to update (empty row or no valid mappings), skip the operation
        if (empty($productData)) {
            return null;
        }

        // Add or update the product by SKU, and only update the mapped fields
        return Product::updateOrCreate(
            ['sku' => $row['sku']], // Assume SKU is the unique identifier
            $productData // Only update the columns that are mapped
        );
    }


    public function rules(): array
    {
        return [
            'sku' => 'required',
            'name' => 'nullable',
            'barcode' => 'nullable',
            'quantity' => 'nullable',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function uniqueBy(): array
    {
        return ['sku'];
    }

    public function upsertColumns(): array
    {
        return ['name', 'barcode', 'quantity'];
    }
}
