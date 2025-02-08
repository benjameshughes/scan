<?php

namespace App\Imports;

use App\Events\ImportedFile;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
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

    public int $totalRows = 0;
    public int $importedRows = 0;

    public function model(array $row)
    {
        $this->importedRows = $this->getRowNumber();

        $progress = ($this->importedRows / $this->totalRows) * 100;
        event(new ImportedFile($progress));

        return new Product([
            'sku' => $row['sku'],
            'name' => $row['name'],
            'barcode' => $row['barcode'],
            'quantity' => $row['quantity'],
        ]);
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
        return 100;
    }

    public function batchSize(): int
    {
        return 100;
    }
}