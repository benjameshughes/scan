<?php

namespace App\Imports;

use App\Events\ImportedFile;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class ProductsImport implements ToModel, WithHeadingRow, ShouldQueue, WithBatchInserts, WithUpserts, WithChunkReading
{
    use Importable, RemembersRowNumber;

    private int $size = 50;
    public int $totalRows = 0;
    public int $importedRows = 0;

    public function model(array $row)
    {
        $this->importedRows = $this->getRowNumber();
        $progress = ($this->importedRows / $this->totalRows) * 100;
        event(new ImportedFile($progress));

        $attributes = [];

        $columns = ['sku', 'name', 'barcode', 'barcode_2', 'quantity'];

        foreach ($columns as $column)
        {
            if (array_key_exists($column, $row)) {
                $attributes[$column] = $row[$column];
            }
        }
        foreach ($row as $key => $value) {
            Log::info($key . ': ' . $value);
        }

        // Pass array of attributes to the model
        return new Product($attributes);
    }

    public function setTotalRows($totalRows)
    {
        $this->totalRows = $totalRows;
    }

    public function uniqueBy()
    {
        return 'sku';
    }

    public function chunkSize(): int
    {
        return $this->size;
    }

    public function batchSize(): int
    {
        return $this->size;
    }
}