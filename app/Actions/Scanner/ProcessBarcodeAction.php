<?php

namespace App\Actions\Scanner;

use App\Actions\GetProductFromScannedBarcode;
use App\DTOs\Scanner\BarcodeResult;
use App\Rules\BarcodePrefixCheck;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProcessBarcodeAction
{
    public function __construct(
        private GetProductFromScannedBarcode $getProductAction,
    ) {}

    /**
     * Process a scanned or manually entered barcode
     */
    public function handle(string $barcode): BarcodeResult
    {
        Log::debug('Processing barcode', ['barcode' => $barcode]);

        // Validate barcode format
        $validator = Validator::make(
            ['barcode' => $barcode],
            ['barcode' => [new BarcodePrefixCheck('505903')]]
        );

        if ($validator->fails()) {
            $error = $validator->errors()->first('barcode');
            Log::debug('Barcode validation failed', ['barcode' => $barcode, 'error' => $error]);

            return BarcodeResult::invalid($barcode, $error);
        }

        try {
            // Lookup product
            $product = $this->getProductAction->handle($barcode);

            if ($product) {
                Log::debug('Product found for barcode', [
                    'barcode' => $barcode,
                    'product_sku' => $product->sku,
                    'product_name' => $product->name,
                ]);

                return BarcodeResult::success($barcode, $product);
            } else {
                Log::debug('No product found for barcode', ['barcode' => $barcode]);

                return BarcodeResult::validButNotFound($barcode);
            }

        } catch (\Exception $e) {
            Log::error('Error processing barcode', [
                'barcode' => $barcode,
                'error' => $e->getMessage(),
            ]);

            return BarcodeResult::invalid($barcode, 'Error processing barcode: '.$e->getMessage());
        }
    }

    /**
     * Process barcode for camera scan (includes additional logging)
     */
    public function handleCameraScan(string $barcode): BarcodeResult
    {
        Log::info('Camera barcode detected', ['barcode' => $barcode]);

        return $this->handle($barcode);
    }

    /**
     * Alias for handleCameraScan for consistency with test usage
     */
    public function handleCameraDetection(string $barcode): BarcodeResult
    {
        return $this->handleCameraScan($barcode);
    }

    /**
     * Process barcode for manual entry
     */
    public function handleManualEntry(string $barcode): BarcodeResult
    {
        Log::info('Manual barcode entered', ['barcode' => $barcode]);

        return $this->handle($barcode);
    }

    /**
     * Process barcode for email refill workflow
     */
    public function handleEmailRefill(string $barcode): BarcodeResult
    {
        Log::info('Email refill barcode processing', ['barcode' => $barcode]);

        $result = $this->handle($barcode);

        // For email refill, we need a product to proceed
        if (! $result->hasProduct() && $result->isValid) {
            Log::warning('Email refill failed - no product found', ['barcode' => $barcode]);

            return BarcodeResult::invalid(
                $barcode,
                'Product not found. Cannot process refill for unknown product.'
            );
        }

        return $result;
    }

    /**
     * Validate barcode format only (without product lookup)
     */
    public function validateBarcodeFormat(string $barcode): bool
    {
        $validator = Validator::make(
            ['barcode' => $barcode],
            ['barcode' => [new BarcodePrefixCheck('505903')]]
        );

        return ! $validator->fails();
    }

    /**
     * Get barcode validation error message
     */
    public function getBarcodeValidationError(string $barcode): ?string
    {
        $validator = Validator::make(
            ['barcode' => $barcode],
            ['barcode' => [new BarcodePrefixCheck('505903')]]
        );

        if ($validator->fails()) {
            return $validator->errors()->first('barcode');
        }

        return null;
    }
}
