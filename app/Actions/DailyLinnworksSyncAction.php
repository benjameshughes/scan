<?php

namespace App\Actions;

use App\Models\PendingProductUpdate;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DailyLinnworksSyncAction
{
    /**
     * Process a batch of Linnworks products
     */
    public function processBatch(array $linnworksProducts, bool $dryRun = false): array
    {
        $stats = ['processed' => 0, 'created' => 0, 'queued' => 0, 'errors' => 0];

        foreach ($linnworksProducts as $linnworksProduct) {
            try {
                // Extract SKU - Linnworks uses ItemNumber as SKU
                $sku = $linnworksProduct['SKU'] ?? $linnworksProduct['ItemNumber'] ?? null;

                if (empty($sku)) {
                    Log::warning('Skipping product without SKU/ItemNumber', ['data' => $linnworksProduct]);

                    continue;
                }
                $localProduct = Product::where('sku', $sku)->first();

                if ($localProduct) {
                    // Product exists - check for updates
                    if ($this->hasChanges($localProduct, $linnworksProduct)) {
                        if (! $dryRun) {
                            $this->queueProductUpdate($localProduct, $linnworksProduct);
                        }
                        $stats['queued']++;
                    }
                } else {
                    // Product doesn't exist - auto-create
                    if (! $dryRun) {
                        $this->createProductFromLinnworks($linnworksProduct);
                    }
                    $stats['created']++;
                }

                $stats['processed']++;

            } catch (\Exception $e) {
                Log::error('Error processing Linnworks product', [
                    'sku' => $linnworksProduct['SKU'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $stats['errors']++;
            }
        }

        return $stats;
    }

    /**
     * Check if local product has changes compared to Linnworks data
     */
    private function hasChanges(Product $localProduct, array $linnworksData): bool
    {
        // Extract barcodes from Linnworks data
        $linnworksBarcode = $this->extractBarcode($linnworksData);
        $linnworksBarcode2 = $this->extractBarcode2($linnworksData);
        $linnworksBarcode3 = $this->extractBarcode3($linnworksData);

        // Extract stock level from StockLevels array
        $linnworksStockLevel = 0;
        if (isset($linnworksData['StockLevels']) && is_array($linnworksData['StockLevels']) && count($linnworksData['StockLevels']) > 0) {
            $linnworksStockLevel = $linnworksData['StockLevels'][0]['StockLevel'] ?? 0;
        }

        // Normalize values for comparison (handle type differences and null/empty)
        $localBarcode = $localProduct->barcode ? (string) $localProduct->barcode : null;
        $localBarcode2 = $localProduct->barcode_2 ?: null;
        $localBarcode3 = $localProduct->barcode_3 ?: null;

        $linnworksBarcode = $linnworksBarcode ?: null;
        $linnworksBarcode2 = $linnworksBarcode2 ?: null;
        $linnworksBarcode3 = $linnworksBarcode3 ?: null;

        return $localProduct->name !== ($linnworksData['ItemTitle'] ?? '') ||
               $localProduct->quantity !== $linnworksStockLevel ||
               $localBarcode !== $linnworksBarcode ||
               $localBarcode2 !== $linnworksBarcode2 ||
               $localBarcode3 !== $linnworksBarcode3;
    }

    /**
     * Create a new product from Linnworks data
     */
    private function createProductFromLinnworks(array $linnworksData): Product
    {
        return DB::transaction(function () use ($linnworksData) {
            // Extract stock level from StockLevels array
            $stockLevel = 0;
            if (isset($linnworksData['StockLevels']) && is_array($linnworksData['StockLevels']) && count($linnworksData['StockLevels']) > 0) {
                $stockLevel = $linnworksData['StockLevels'][0]['StockLevel'] ?? 0;
            }

            $sku = $linnworksData['SKU'] ?? $linnworksData['ItemNumber'] ?? 'UNKNOWN';

            return Product::create([
                'sku' => $sku,
                'name' => $linnworksData['ItemTitle'] ?? 'Unknown Product',
                'barcode' => $this->extractBarcode($linnworksData),
                'barcode_2' => $this->extractBarcode2($linnworksData),
                'barcode_3' => $this->extractBarcode3($linnworksData),
                'quantity' => $stockLevel,
                'linnworks_id' => $linnworksData['StockItemId'] ?? null,
                'auto_synced' => true,
                'last_synced_at' => now(),
            ]);
        });
    }

    /**
     * Queue a product update for manual review
     */
    private function queueProductUpdate(Product $localProduct, array $linnworksData): void
    {
        $changes = $this->getChanges($localProduct, $linnworksData);

        // Separate auto-acceptable changes from manual review changes
        $autoAcceptableChanges = $this->getAutoAcceptableChanges($changes);
        $manualReviewChanges = $this->getManualReviewChanges($changes);

        DB::transaction(function () use ($localProduct, $linnworksData, $autoAcceptableChanges, $manualReviewChanges) {
            // Auto-accept stock changes if enabled
            if (! empty($autoAcceptableChanges)) {
                $this->autoAcceptChanges($localProduct, $linnworksData, $autoAcceptableChanges);
            }

            // Queue remaining changes for manual review
            if (! empty($manualReviewChanges)) {
                PendingProductUpdate::updateOrCreate(
                    [
                        'product_id' => $localProduct->id,
                        'status' => 'pending',
                    ],
                    [
                        'linnworks_data' => $linnworksData,
                        'changes_detected' => $manualReviewChanges,
                        'created_at' => now(),
                    ]
                );
            }
        });
    }

    /**
     * Get the specific changes between local and Linnworks data
     */
    private function getChanges(Product $localProduct, array $linnworksData): array
    {
        $changes = [];

        if ($localProduct->name !== ($linnworksData['ItemTitle'] ?? '')) {
            $changes['name'] = [
                'local' => $localProduct->name,
                'linnworks' => $linnworksData['ItemTitle'] ?? '',
            ];
        }

        // Extract stock level from StockLevels array
        $linnworksStockLevel = 0;
        if (isset($linnworksData['StockLevels']) && is_array($linnworksData['StockLevels']) && count($linnworksData['StockLevels']) > 0) {
            $linnworksStockLevel = $linnworksData['StockLevels'][0]['StockLevel'] ?? 0;
        }

        if ($localProduct->quantity !== $linnworksStockLevel) {
            $changes['quantity'] = [
                'local' => $localProduct->quantity,
                'linnworks' => $linnworksStockLevel,
            ];
        }

        // Normalize values for comparison (same as hasChanges method)
        $localBarcode = $localProduct->barcode ? (string) $localProduct->barcode : null;
        $localBarcode2 = $localProduct->barcode_2 ?: null;
        $localBarcode3 = $localProduct->barcode_3 ?: null;

        $linnworksBarcode = $this->extractBarcode($linnworksData) ?: null;
        $linnworksBarcode2 = $this->extractBarcode2($linnworksData) ?: null;
        $linnworksBarcode3 = $this->extractBarcode3($linnworksData) ?: null;

        if ($localBarcode !== $linnworksBarcode) {
            $changes['barcode'] = [
                'local' => $localBarcode,
                'linnworks' => $linnworksBarcode,
            ];
        }

        if ($localBarcode2 !== $linnworksBarcode2) {
            $changes['barcode_2'] = [
                'local' => $localBarcode2,
                'linnworks' => $linnworksBarcode2,
            ];
        }

        if ($localBarcode3 !== $linnworksBarcode3) {
            $changes['barcode_3'] = [
                'local' => $localBarcode3,
                'linnworks' => $linnworksBarcode3,
            ];
        }

        return $changes;
    }

    /**
     * Get changes that can be auto-accepted based on configuration
     */
    private function getAutoAcceptableChanges(array $changes): array
    {
        $autoAcceptable = [];

        if (config('linnworks.sync_behavior.auto_accept_stock_changes') && isset($changes['quantity'])) {
            $autoAcceptable['quantity'] = $changes['quantity'];
        }

        if (config('linnworks.sync_behavior.auto_accept_name_changes') && isset($changes['name'])) {
            $autoAcceptable['name'] = $changes['name'];
        }

        if (config('linnworks.sync_behavior.auto_accept_barcode_changes')) {
            foreach (['barcode', 'barcode_2', 'barcode_3'] as $barcodeField) {
                if (isset($changes[$barcodeField])) {
                    $autoAcceptable[$barcodeField] = $changes[$barcodeField];
                }
            }
        }

        return $autoAcceptable;
    }

    /**
     * Get changes that require manual review
     */
    private function getManualReviewChanges(array $changes): array
    {
        $autoAcceptable = $this->getAutoAcceptableChanges($changes);

        // Return changes that are not auto-acceptable
        return array_diff_key($changes, $autoAcceptable);
    }

    /**
     * Auto-accept changes and log them for audit trail
     */
    private function autoAcceptChanges(Product $localProduct, array $linnworksData, array $changes): void
    {
        // Apply the changes to the product
        $updateData = [];

        if (isset($changes['quantity'])) {
            $updateData['quantity'] = $changes['quantity']['linnworks'];
        }

        if (isset($changes['name'])) {
            $updateData['name'] = $changes['name']['linnworks'];
        }

        if (isset($changes['barcode'])) {
            $updateData['barcode'] = $changes['barcode']['linnworks'];
        }

        if (isset($changes['barcode_2'])) {
            $updateData['barcode_2'] = $changes['barcode_2']['linnworks'];
        }

        if (isset($changes['barcode_3'])) {
            $updateData['barcode_3'] = $changes['barcode_3']['linnworks'];
        }

        // Update the product
        if (! empty($updateData)) {
            $localProduct->update($updateData);

            // Log the auto-accepted change for audit trail
            PendingProductUpdate::create([
                'product_id' => $localProduct->id,
                'linnworks_data' => $linnworksData,
                'changes_detected' => $changes,
                'status' => 'auto_accepted',
                'accepted_at' => now(),
                'accepted_by' => null, // System auto-acceptance
                'created_at' => now(),
            ]);
        }
    }

    /**
     * Extract primary barcode from Linnworks data
     */
    private function extractBarcode(array $data): ?string
    {
        // Check multiple possible locations for barcode
        return $data['BarcodeNumber'] ??
               $data['Barcode'] ??
               $data['ItemNumber'] ??
               null;
    }

    /**
     * Extract secondary barcode from Linnworks data
     */
    private function extractBarcode2(array $data): ?string
    {
        if (isset($data['AdditionalBarcodes']) && is_array($data['AdditionalBarcodes']) && count($data['AdditionalBarcodes']) > 0) {
            return $data['AdditionalBarcodes'][0] ?? null;
        }

        return null;
    }

    /**
     * Extract tertiary barcode from Linnworks data
     */
    private function extractBarcode3(array $data): ?string
    {
        if (isset($data['AdditionalBarcodes']) && is_array($data['AdditionalBarcodes']) && count($data['AdditionalBarcodes']) > 1) {
            return $data['AdditionalBarcodes'][1] ?? null;
        }

        return null;
    }
}
