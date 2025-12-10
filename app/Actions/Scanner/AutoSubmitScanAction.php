<?php

namespace App\Actions\Scanner;

use App\DTOs\Scanner\ScanData;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class AutoSubmitScanAction
{
    public function __construct(
        private CreateScanRecordAction $createScanRecordAction,
        private ValidateScanDataAction $validateScanDataAction,
    ) {}

    /**
     * Handle auto-submit for a scanned product
     *
     * Auto-submit automatically creates a scan record with default values:
     * - Quantity: 1 (default)
     * - Action: decrease (default)
     * - Submitted: false (will be synced)
     */
    public function handle(Product $product, string $barcode, int $userId): array
    {
        Log::info('Auto-submit triggered', [
            'product_sku' => $product->sku,
            'product_name' => $product->name,
            'barcode' => $barcode,
            'user_id' => $userId,
        ]);

        try {
            // Create scan data with default values for auto-submit
            $scanData = ScanData::fromForm(
                barcode: $barcode,
                quantity: 1, // Auto-submit always uses quantity 1
                scanAction: false, // Default action is decrease (false)
                userId: $userId
            );

            // Validate scan data
            $this->validateScanDataAction->validateOrFail($scanData);

            // Create scan record
            $scan = $this->createScanRecordAction->handle($scanData);

            Log::info('Auto-submit successful', [
                'scan_id' => $scan->id,
                'product_sku' => $product->sku,
                'user_id' => $userId,
            ]);

            return [
                'success' => true,
                'scan_id' => $scan->id,
                'message' => 'Scan auto-submitted successfully',
            ];

        } catch (\Exception $e) {
            Log::error('Auto-submit failed', [
                'product_sku' => $product->sku,
                'barcode' => $barcode,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Auto-submit failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Check if auto-submit should be triggered
     *
     * Auto-submit should only trigger when:
     * - Product was found
     * - User has auto-submit enabled in settings
     * - Barcode was detected via camera (not manual entry)
     */
    public function shouldAutoSubmit(
        ?Product $product,
        bool $autoSubmitEnabled,
        bool $isCameraDetection = true
    ): bool {
        return $product !== null
            && $autoSubmitEnabled
            && $isCameraDetection;
    }
}
