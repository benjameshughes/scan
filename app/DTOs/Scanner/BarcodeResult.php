<?php

namespace App\DTOs\Scanner;

use App\Models\Product;

readonly class BarcodeResult
{
    public function __construct(
        public string $barcode,
        public bool $isValid,
        public ?Product $product,
        public ?string $error = null,
        public bool $shouldTriggerFeedback = false,
    ) {}

    /**
     * Create a successful result with product found
     */
    public static function success(string $barcode, Product $product): self
    {
        return new self(
            barcode: $barcode,
            isValid: true,
            product: $product,
            shouldTriggerFeedback: true,
        );
    }

    /**
     * Create a valid result but no product found
     */
    public static function validButNotFound(string $barcode): self
    {
        return new self(
            barcode: $barcode,
            isValid: true,
            product: null,
            shouldTriggerFeedback: false,
        );
    }

    /**
     * Create an invalid result (validation failed)
     */
    public static function invalid(string $barcode, string $error): self
    {
        return new self(
            barcode: $barcode,
            isValid: false,
            product: null,
            error: $error,
            shouldTriggerFeedback: false,
        );
    }

    /**
     * Check if product was found
     */
    public function hasProduct(): bool
    {
        return $this->product !== null;
    }

    /**
     * Check if result has an error
     */
    public function hasError(): bool
    {
        return ! empty($this->error);
    }

    /**
     * Get display status for UI
     */
    public function getStatus(): string
    {
        if (! $this->isValid) {
            return 'invalid';
        }

        if ($this->hasProduct()) {
            return 'found';
        }

        return 'not_found';
    }

    /**
     * Convert to array for component state
     */
    public function toComponentState(): array
    {
        return [
            'barcode' => $this->barcode,
            'barcodeScanned' => $this->isValid,
            'product' => $this->product,
            'playSuccessSound' => $this->shouldTriggerFeedback,
            'triggerVibration' => $this->shouldTriggerFeedback,
        ];
    }
}
