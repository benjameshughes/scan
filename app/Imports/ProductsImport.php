<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class ProductsImport implements ToModel, WithHeadingRow, ShouldQueue, WithBatchInserts, WithUpserts, WithChunkReading
{
    use RemembersRowNumber;

    public function model(array $row)
    {

        $currentRowNumber = $this->getRowNumber();
        return new Product([
            'sku' => $row['sku'],
            'name' => $row['name'],
            'barcode' => $row['barcode'],
            'quantity' => $row['quantity'],
        ]);
    }

    public function uniqueBy(): array
    {
        return ['sku'];
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