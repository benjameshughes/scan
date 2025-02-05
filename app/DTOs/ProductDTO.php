<?php

namespace App\DTOs;

readonly class ProductDTO
{
    public function __construct(
        public string  $sku,
        public ?string $name = null,
        public ?string $barcode = null,
        public ?int    $quantity = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sku: $data['sku'],
            name: $data['name'] ?? null,
            barcode: $data['barcode'] ?? null,
            quantity: isset($data['quantity']) ? (int) $data['quantity'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'sku' => $this->sku,
            'name' => $this->name,
            'barcode' => $this->barcode,
            'quantity' => $this->quantity,
        ], fn($value) => !is_null($value));
    }
}