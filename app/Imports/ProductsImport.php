<?php

namespace App\Imports;

use App\DTOs\ProductDTO;
use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;

class ProductsImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithValidation, SkipsOnError
{
    use Importable, SkipsErrors;

    private array $mappings;
    private array $results = [
        'created' => 0,
        'updated' => 0,
        'failed' => 0,
    ];

    public function __construct(array $mappings)
    {
        $this->mappings = $mappings;
    }

    public function collection(Collection $rows): void
    {
        $rows->each(function ($row) {
            try {
                $this->processRow($row);
            } catch (\Exception $e) {
                $this->results['failed']++;
                Log::error("Import failed for row", [
                    'row' => $row,
                    'error' => $e->getMessage()
                ]);
            }
        });
    }

    private function processRow($row): void
    {
        $productData = $this->mapRowToData($row);

        if (empty($productData)) {
            $this->results['failed']++;
            return;
        }

        $dto = ProductDTO::fromArray($productData);

        $product = Product::where('sku', $dto->sku)->first();

        if ($product) {
            $this->updateProduct($product, $dto);
            $this->results['updated']++;
        } else {
            $this->createProduct($dto);
            $this->results['created']++;
        }
    }

    private function mapRowToData($row): array
    {
        $productData = [];

        foreach ($this->mappings as $modelColumn => $fileColumn) {
            if (isset($row[$fileColumn])) {
                $productData[$modelColumn] = $this->formatValue($row[$fileColumn], $modelColumn);
            }
        }

        return $productData;
    }

    private function formatValue($value, string $column)
    {
        return match ($column) {
            'quantity' => (int) $value,
            'price' => (float) $value,
            default => $value,
        };
    }

    private function updateProduct(Product $product, ProductDTO $dto): void
    {
        $product->fill($dto->toArray());
        $product->save();
    }

    private function createProduct(ProductDTO $dto): void
    {
        Product::updateOrCreate($dto->toArray());
    }

    public function rules(): array
    {
        return [
            '*.sku' => 'required|string',
            '*.name' => 'nullable|string',
            '*.barcode' => 'nullable|string',
            '*.quantity' => 'nullable|integer',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function getResults(): array
    {
        return $this->results;
    }
}