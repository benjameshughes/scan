<?php

namespace App\Actions;

use App\Actions\Concerns\SendNotifications;
use App\Actions\Concerns\UpdateScanStatus;
use App\Actions\Contracts\Action;
use App\Models\Scan;
use App\Services\LinnworksApiService;
use Illuminate\Support\Facades\Log;

final class SyncBarcodeAction implements Action
{
    use SendNotifications, UpdateScanStatus;

    public Scan $scan;

    public LinnworksApiService $linnworks;

    public function __construct(Scan $scan)
    {
        $this->scan = $scan;
        $this->linnworks = new LinnworksApiService;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        // Let's first check the scan has not already been submitted as resubmitted would be a bit silly
        if ($this->scan->submitted) {
            return;
        }

        try {
            $this->updateScanStatus($this->scan, 'syncing');

            // Check barcode exists and has a SKU, if null stop
            $product = new CheckBarcodeExists($this->scan)->handle();

            if (empty($product)) {
                // This should not happen with pre-scan validation, but handle gracefully
                $errorMessage = 'Product not found for barcode '.$this->scan->barcode;
                $this->markScanAsFailed($this->scan, $errorMessage, 'product_not_found', [
                    'barcode' => $this->scan->barcode,
                    'scan_id' => $this->scan->id,
                ]);

                Log::channel('inventory')->error($errorMessage.' - This should not occur with validation');
                throw new \Exception($errorMessage);
            }

            // Get the SKU of the product
            $sku = $product->sku;

            // Get the stock level from Linnworks using the SKU
            $lwStockLevel = $this->linnworks->getStockLevel($sku);

            // Calculate the new stock level based on the action
            $stockAction = new LinnworksStockAction($this->scan, $lwStockLevel);
            $newStockLevel = $stockAction->handle();

            // Update the stock level
            $this->linnworks->updateStockLevel($sku, $newStockLevel);

            // Record the stock change on the scan
            $this->scan->update([
                'stock_before' => $lwStockLevel,
                'stock_after' => $newStockLevel,
            ]);

            // Update scan as synced
            $this->markScanAsSuccessful($this->scan);

        } catch (\Throwable $exception) {
            // Use enhanced error categorization
            [$errorType, $errorMessage] = $this->categorizeError($exception);

            // Add context metadata
            $metadata = [
                'scan_id' => $this->scan->id,
                'barcode' => $this->scan->barcode,
                'exception_class' => get_class($exception),
                'trace_hash' => hash('sha256', $exception->getTraceAsString()),
            ];

            // Add product context if available
            if (isset($product) && $product) {
                $metadata['sku'] = $product->sku;
                $metadata['product_id'] = $product->id;
            }

            $this->markScanAsFailed($this->scan, $errorMessage, $errorType, $metadata);

            Log::channel('inventory')->error('Sync failed for scan '.$this->scan->id, [
                'error_type' => $errorType,
                'error_message' => $errorMessage,
                'attempt' => $this->scan->sync_attempts,
                'metadata' => $metadata,
            ]);

            // Fire event to notify relevant users about the scan failure
            event(new \App\Events\ScanSyncFailed(
                $this->scan,
                $errorMessage,
                $errorType
            ));

            // Re-throw if this is a non-recoverable error or max attempts reached
            if (! $this->shouldRetry($this->scan, $errorType)) {
                throw $exception;
            }

            // For retryable errors, we'll let the job system handle the retry
            // The job will be delayed based on the retry logic
            throw $exception;
        }
    }
}
