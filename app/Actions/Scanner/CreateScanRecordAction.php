<?php

namespace App\Actions\Scanner;

use App\DTOs\Scanner\ScanData;
use App\Jobs\SyncBarcode;
use App\Models\Scan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateScanRecordAction
{
    /**
     * Create a new scan record and dispatch sync job
     */
    public function handle(ScanData $scanData): Scan
    {
        return DB::transaction(function () use ($scanData) {
            Log::info('Creating scan record', [
                'barcode' => $scanData->barcode,
                'quantity' => $scanData->quantity,
                'action' => $scanData->action,
                'user_id' => $scanData->userId,
            ]);

            // Create the scan record
            $scan = Scan::create($scanData->toScanAttributes());

            // Dispatch background sync job
            SyncBarcode::dispatch($scan);

            // Log the scan for audit trail
            Log::channel('barcode')->info("{$scanData->barcode} Scanned", [
                'scan_id' => $scan->id,
                'quantity' => $scanData->quantity,
                'action' => $scanData->action,
                'user_id' => $scanData->userId,
                'metadata' => $scanData->metadata,
            ]);

            Log::info('Scan record created successfully', [
                'scan_id' => $scan->id,
                'barcode' => $scanData->barcode,
            ]);

            return $scan;
        });
    }

    /**
     * Create scan with additional metadata
     */
    public function handleWithMetadata(ScanData $scanData, array $metadata): Scan
    {
        $enrichedScanData = $scanData->withMetadata($metadata);

        return $this->handle($enrichedScanData);
    }

    /**
     * Create multiple scan records (batch operation)
     */
    public function handleBatch(array $scanDataItems): array
    {
        return DB::transaction(function () use ($scanDataItems) {
            $scans = [];

            foreach ($scanDataItems as $scanData) {
                if (! $scanData instanceof ScanData) {
                    throw new \InvalidArgumentException('All items must be ScanData instances');
                }

                $scans[] = $this->handle($scanData);
            }

            Log::info('Batch scan records created', [
                'count' => count($scans),
                'scan_ids' => collect($scans)->pluck('id')->toArray(),
            ]);

            return $scans;
        });
    }

    /**
     * Create scan for auto-submit workflow
     */
    public function handleAutoSubmit(ScanData $scanData): Scan
    {
        Log::info('Creating auto-submit scan record', [
            'barcode' => $scanData->barcode,
            'user_id' => $scanData->userId,
        ]);

        $metadata = array_merge($scanData->metadata, [
            'auto_submitted' => true,
            'submitted_at' => now()->toISOString(),
        ]);

        return $this->handleWithMetadata($scanData, $metadata);
    }
}
